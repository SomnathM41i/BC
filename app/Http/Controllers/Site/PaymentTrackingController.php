<?php

namespace App\Http\Controllers\Site;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Employee;
use App\Models\Project;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Support\Money;
use App\Support\PhoneNumber;
use App\Support\Site\SiteRoutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PaymentTrackingController extends Controller
{
    private const CATEGORIES = ['client', 'contractor', 'employee'];

    public function __construct(private readonly WalletService $wallets) {}

    public function index(): View
    {
        return view('site.payment-tracking.index');
    }

    public function selectCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'in:client,contractor,employee'],
        ]);

        $request->session()->forget('payment_tracking');
        $request->session()->put('payment_tracking.pending_category', $validated['category']);

        return redirect()->to(SiteRoutes::url('payment-tracking.verify'));
    }

    /**
     * One-step lookup used by the homepage quick form: role and mobile in a
     * single POST, so visitors do not have to go through two screens.
     */
    public function lookup(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => ['required', 'string', 'in:client,contractor,employee'],
            'mobile' => ['required', 'string', 'max:20'],
        ], [
            'category.required' => __('site.payment_tracking.choose_category'),
            'category.in' => __('site.payment_tracking.choose_category'),
            'mobile.required' => __('site.payment_tracking.invalid_mobile'),
            'mobile.max' => __('site.payment_tracking.invalid_mobile'),
        ]);

        if ($validator->fails()) {
            return redirect()
                ->to(SiteRoutes::url('home').'#track-payments')
                ->withInput($request->only('category', 'mobile'))
                ->withErrors($validator);
        }

        $validated = $validator->validated();
        $mobile = PhoneNumber::normalize((string) $validated['mobile']);

        if ($mobile === null) {
            return redirect()
                ->to(SiteRoutes::url('home').'#track-payments')
                ->withInput($request->only('category', 'mobile'))
                ->withErrors(['mobile' => __('site.payment_tracking.invalid_mobile')]);
        }

        $request->session()->forget('payment_tracking');
        $request->session()->put('payment_tracking.pending_category', $validated['category']);

        return match ($validated['category']) {
            'client' => $this->verifyClient($request, $mobile, true),
            'contractor' => $this->verifyContractor($request, $mobile, true),
            'employee' => $this->verifyEmployee($request, $mobile, true),
        };
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        $category = $request->session()->get('payment_tracking.pending_category');

        if (! in_array($category, self::CATEGORIES, true)) {
            return redirect()
                ->to(SiteRoutes::url('payment-tracking.index'))
                ->with('tracking_error', __('site.payment_tracking.choose_category'));
        }

        return view('site.payment-tracking.verify', [
            'category' => $category,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $category = $request->session()->get('payment_tracking.pending_category');

        if (! in_array($category, self::CATEGORIES, true)) {
            return redirect()
                ->to(SiteRoutes::url('payment-tracking.index'))
                ->with('tracking_error', __('site.payment_tracking.choose_category'));
        }

        $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
        ], [
            'mobile.required' => __('site.payment_tracking.invalid_mobile'),
            'mobile.max' => __('site.payment_tracking.invalid_mobile'),
        ]);

        $mobile = PhoneNumber::normalize((string) $request->input('mobile'));

        if ($mobile === null) {
            return redirect()
                ->to(SiteRoutes::url('payment-tracking.verify'))
                ->withInput()
                ->withErrors(['mobile' => __('site.payment_tracking.invalid_mobile')]);
        }

        return match ($category) {
            'client' => $this->verifyClient($request, $mobile),
            'contractor' => $this->verifyContractor($request, $mobile),
            'employee' => $this->verifyEmployee($request, $mobile),
        };
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $data = $request->session()->get('payment_tracking');

        return match ($data['category'] ?? null) {
            'client' => $this->clientDashboard($request, $data),
            'contractor' => $this->contractorDashboard($request, $data),
            'employee' => $this->employeeDashboard($request, $data),
            default => $this->expireSession($request),
        };
    }

    public function switchProject(Request $request): RedirectResponse
    {
        $data = $request->session()->get('payment_tracking');

        if (($data['category'] ?? null) !== 'client') {
            return $this->expireSession($request);
        }

        $validated = $request->validate([
            'project_id' => ['required', 'integer'],
        ]);

        $allowed = array_map('intval', (array) ($data['project_ids'] ?? []));

        if (! in_array((int) $validated['project_id'], $allowed, true)) {
            return redirect()
                ->to(SiteRoutes::url('payment-tracking.dashboard'))
                ->with('tracking_error', __('site.payment_tracking.project_not_available'));
        }

        $request->session()->put('payment_tracking.selected_project_id', (int) $validated['project_id']);

        return redirect()->to(SiteRoutes::url('payment-tracking.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('payment_tracking');
        $request->session()->regenerate();

        return redirect()
            ->to(SiteRoutes::url('payment-tracking.index'))
            ->with('tracking_status', __('site.payment_tracking.logged_out'));
    }

    private function verifyClient(Request $request, string $mobile, bool $fromHome = false): RedirectResponse
    {
        $projects = Project::query()
            ->where('client_mobile_normalized', $mobile)
            ->orderBy('name')
            ->get();

        if ($projects->isEmpty()) {
            return $this->lookupFailed($request, $fromHome);
        }

        $projectIds = $projects->pluck('id')->map(fn ($id) => (int) $id)->all();

        return $this->verified($request, [
            'category' => 'client',
            'project_ids' => $projectIds,
            'selected_project_id' => $projectIds[0],
        ]);
    }

    private function verifyContractor(Request $request, string $mobile, bool $fromHome = false): RedirectResponse
    {
        $matches = Contractor::query()
            ->get()
            ->filter(fn (Contractor $contractor) => PhoneNumber::normalize($contractor->phone) === $mobile)
            ->values();

        if ($matches->count() !== 1) {
            return $this->lookupFailed($request, $fromHome);
        }

        return $this->verified($request, [
            'category' => 'contractor',
            'contractor_id' => (int) $matches->first()->id,
        ]);
    }

    private function verifyEmployee(Request $request, string $mobile, bool $fromHome = false): RedirectResponse
    {
        $matches = Employee::query()
            ->get()
            ->filter(fn (Employee $employee) => PhoneNumber::normalize($employee->phone_number) === $mobile)
            ->values();

        if ($matches->count() !== 1) {
            return $this->lookupFailed($request, $fromHome);
        }

        return $this->verified($request, [
            'category' => 'employee',
            'employee_id' => (int) $matches->first()->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $identity
     */
    private function verified(Request $request, array $identity): RedirectResponse
    {
        $request->session()->regenerate();
        $request->session()->put('payment_tracking', $identity + [
            'verified_at' => now()->timestamp,
        ]);

        return redirect()
            ->to(SiteRoutes::url('payment-tracking.dashboard'))
            ->with('tracking_status', __('site.payment_tracking.verified'));
    }

    private function lookupFailed(Request $request, bool $fromHome = false): RedirectResponse
    {
        $redirect = $fromHome
            ? redirect()->to(SiteRoutes::url('home').'#track-payments')
            : redirect()->to(SiteRoutes::url('payment-tracking.verify'));

        return $redirect
            ->withInput($request->only('category', 'mobile'))
            ->with('tracking_error', __('site.payment_tracking.lookup_failed'));
    }

    private function expireSession(Request $request): RedirectResponse
    {
        $request->session()->forget('payment_tracking');

        return redirect()
            ->to(SiteRoutes::url('payment-tracking.index'))
            ->with('tracking_error', __('site.payment_tracking.session_expired'));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function clientDashboard(Request $request, array $data): View|RedirectResponse
    {
        $projects = Project::query()
            ->whereIn('id', (array) ($data['project_ids'] ?? []))
            ->with(['receipts' => fn ($query) => $query->recentFirst()])
            ->orderBy('name')
            ->get();

        if ($projects->isEmpty()) {
            return $this->expireSession($request);
        }

        $selectedId = (int) ($data['selected_project_id'] ?? 0);
        $selected = $projects->firstWhere('id', $selectedId) ?? $projects->first();

        if ((int) $selected->id !== $selectedId) {
            $request->session()->put('payment_tracking.selected_project_id', (int) $selected->id);
        }

        $totalAgreed = Money::sum($projects, fn (Project $project) => (string) $project->agreed_amount);
        $totalReceived = Money::sum($projects, fn (Project $project) => $project->totalReceived());

        return view('site.payment-tracking.dashboard', [
            'category' => 'client',
            'tracking' => $data,
            'projects' => $projects,
            'selectedProject' => $selected,
            'receipts' => $selected->receipts,
            'totalAgreed' => $totalAgreed,
            'totalReceived' => $totalReceived,
            'totalRemaining' => Money::sub($totalAgreed, $totalReceived),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function contractorDashboard(Request $request, array $data): View|RedirectResponse
    {
        $contractor = Contractor::query()->find((int) ($data['contractor_id'] ?? 0));

        if (! $contractor) {
            return $this->expireSession($request);
        }

        $contractor->load('type');

        $transactions = WalletTransaction::query()
            ->where('contractor_id', $contractor->id)
            ->ofType(TransactionType::ContractorTransfer)
            ->with('project')
            ->recentFirst()
            ->get();

        return view('site.payment-tracking.dashboard', [
            'category' => 'contractor',
            'tracking' => $data,
            'contractor' => $contractor,
            'report' => $this->wallets->contractorReport($contractor),
            'transactions' => $transactions,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function employeeDashboard(Request $request, array $data): View|RedirectResponse
    {
        $employee = Employee::query()->find((int) ($data['employee_id'] ?? 0));

        if (! $employee) {
            return $this->expireSession($request);
        }

        $payments = $employee->salaryPayments()
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        return view('site.payment-tracking.dashboard', [
            'category' => 'employee',
            'tracking' => $data,
            'employee' => $employee,
            'payments' => $payments,
            'totalPaid' => Money::sum($payments, fn ($payment) => (string) $payment->amount_paid),
            'totalDue' => Money::sum($payments, fn ($payment) => (string) $payment->remaining_salary_due),
            'outstandingAdvance' => $employee->outstandingAdvance(),
        ]);
    }
}
