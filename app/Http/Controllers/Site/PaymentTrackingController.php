<?php

namespace App\Http\Controllers\Site;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Project;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Support\Money;
use App\Support\PhoneNumber;
use App\Support\Site\SiteRoutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PaymentTrackingController extends Controller
{
    private const CATEGORIES = ['client', 'contractor', 'employee'];

    public function __construct(
        private readonly WalletService $wallets,
    ) {}

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
     * The home page card picks the role and number on one form.
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

        return $this->signIn($request, $this->identityFor($validated['category'], $mobile), true);
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

        return $this->signIn($request, $this->identityFor($category, $mobile));
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

    /**
     * @return array<string, mixed>|null
     */
    private function clientIdentity(string $mobile): ?array
    {
        $projectIds = Project::query()
            ->where('client_mobile_normalized', $mobile)
            ->orderBy('name')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($projectIds === []) {
            return null;
        }

        return [
            'category' => 'client',
            'project_ids' => $projectIds,
            'selected_project_id' => $projectIds[0],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function contractorIdentity(string $mobile): ?array
    {
        $matches = Contractor::query()
            ->get()
            ->filter(fn (Contractor $contractor) => PhoneNumber::normalize($contractor->phone) === $mobile)
            ->values();

        return $this->hasSingleMatch('contractor', $matches)
            ? ['category' => 'contractor', 'contractor_id' => (int) $matches->first()->id]
            : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function employeeIdentity(string $mobile): ?array
    {
        $matches = Employee::query()
            ->get()
            ->filter(fn (Employee $employee) => PhoneNumber::normalize($employee->phone_number) === $mobile)
            ->values();

        return $this->hasSingleMatch('employee', $matches)
            ? ['category' => 'employee', 'employee_id' => (int) $matches->first()->id]
            : null;
    }

    /**
     * A number shared by two records cannot say whose payments to show, so it
     * gets no access; the log tells the office which records to correct.
     */
    private function hasSingleMatch(string $category, Collection $matches): bool
    {
        if ($matches->count() > 1) {
            Log::warning('Payment tracking: one mobile number matches several records.', [
                'category' => $category,
                'ids' => $matches->pluck('id')->all(),
            ]);
        }

        return $matches->count() === 1;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function identityFor(string $category, string $mobile): ?array
    {
        return match ($category) {
            'client' => $this->clientIdentity($mobile),
            'contractor' => $this->contractorIdentity($mobile),
            'employee' => $this->employeeIdentity($mobile),
        };
    }

    /**
     * @param  array<string, mixed>|null  $identity
     */
    private function signIn(Request $request, ?array $identity, bool $fromHome = false): RedirectResponse
    {
        if ($identity === null) {
            $target = $fromHome
                ? SiteRoutes::url('home').'#track-payments'
                : SiteRoutes::url('payment-tracking.verify');

            return redirect()
                ->to($target)
                ->withInput($request->only('category', 'mobile'))
                ->with('tracking_error', __('site.payment_tracking.lookup_failed'));
        }

        return $this->verified($request, $identity);
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

        // Each history list gets its own page parameter so paginating one
        // table does not move the other.
        $attendances = $employee->attendances()
            ->orderByDesc('attendance_date')
            ->paginate(10, ['*'], 'attendance_page')
            ->withQueryString();

        $advances = $employee->advances()
            ->orderByDesc('advance_date')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'advance_page')
            ->withQueryString();

        return view('site.payment-tracking.dashboard', [
            'category' => 'employee',
            'tracking' => $data,
            'employee' => $employee->load('type'),
            'payments' => $payments,
            'totalPaid' => Money::sum($payments, fn ($payment) => (string) $payment->amount_paid),
            'totalDue' => Money::sum($payments, fn ($payment) => (string) $payment->remaining_salary_due),
            'totalAdvances' => $employee->totalAdvances(),
            'totalRepayments' => $employee->totalRepayments(),
            'outstandingAdvance' => $employee->outstandingAdvance(),
            'week' => $this->employeeWeek($employee),
            'attendances' => $attendances,
            'advances' => $advances,
        ]);
    }

    /**
     * The salary week containing today, with a row for every day so unmarked
     * days are visible too.
     *
     * @return array{start: Carbon, end: Carbon, days: list<array{date: Carbon, row: ?EmployeeAttendance}>, present: int, absent: int, earned: string}
     */
    private function employeeWeek(Employee $employee): array
    {
        $period = $employee->getWeekContaining(now());

        $rows = $employee->attendances()
            ->whereDate('attendance_date', '>=', $period['start_date'])
            ->whereDate('attendance_date', '<=', $period['end_date'])
            ->get()
            ->keyBy(fn (EmployeeAttendance $row) => $row->attendance_date->toDateString());

        $days = [];
        for ($date = $period['start']->copy(); $date->lte($period['end']); $date->addDay()) {
            $days[] = ['date' => $date->copy(), 'row' => $rows->get($date->toDateString())];
        }

        return [
            'start' => $period['start'],
            'end' => $period['end'],
            'days' => $days,
            'present' => $rows->where('status', 'present')->count(),
            'absent' => $rows->where('status', 'absent')->count(),
            'earned' => Money::sum($rows, fn (EmployeeAttendance $row) => (string) $row->amount),
        ];
    }
}
