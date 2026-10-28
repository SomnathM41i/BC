<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\TransferToContractorRequest;
use App\Http\Requests\Admin\UpdateProjectClientRequest;
use App\Models\Contractor;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Services\WalletService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function index(Request $request): View
    {
        $projects = Project::query()
            ->with(['contractor', 'receipts', 'wallet'])
            ->orderByRaw(
                "CASE status WHEN 'active' THEN 0 WHEN 'closure_pending' THEN 1 ELSE 2 END"
            )
            ->orderBy('name')
            ->get();

        $rows = $projects->map(fn (Project $project) => [
            'project' => $project,
            'report' => $this->wallets->projectReport($project),
        ]);

        return view('admin.projects.index', [
            'rows' => $rows,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('admin.projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create([
            'name' => $request->validated('name'),
            'location' => $request->validated('location'),
            'client_name' => $request->validated('client_name'),
            'client_mobile' => trim((string) $request->validated('client_mobile')),
            'client_mobile_normalized' => PhoneNumber::normalize($request->validated('client_mobile')),
            'agreed_amount' => $request->validated('agreed_amount'),
            'contractor_id' => $request->validated('contractor_id'),
            'status' => ProjectStatus::Active,
        ]);

        $this->wallets->projectWallet($project);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('status', 'Project created successfully.');
    }

    public function show(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $project->load(['contractor', 'wallet', 'receipts']);

        $transactions = $project->transactions()
            ->with(['category', 'contractor', 'creator'])
            ->recentFirst()
            ->get();

        return view('admin.projects.show', [
            'project' => $project,
            'report' => $this->wallets->projectReport($project),
            'unallocated' => $this->wallets->unallocatedFunds(),
            'isOwner' => true,
            'transactions' => $transactions,
            'projectCategories' => ExpenseCategory::query()->forProject()->active()->orderBy('name')->get(),
            'contractors' => Contractor::query()->active()->orderBy('name')->get(),
            'newContractorSentinel' => TransferToContractorRequest::NEW_CONTRACTOR,
        ]);
    }

    public function updateClient(UpdateProjectClientRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update([
            'client_name' => $request->validated('client_name'),
            'client_mobile' => trim((string) $request->validated('client_mobile')),
            'client_mobile_normalized' => PhoneNumber::normalize($request->validated('client_mobile')),
        ]);

        return back()->with('status', 'Client contact updated. The payment tracking portal can now find this project.');
    }

    public function requestClosure(Project $project): RedirectResponse
    {
        $this->authorize('close', $project);

        $this->wallets->requestProjectClosure($project, request()->user());

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('status', 'Project moved to Closure Pending. New expenses and transfers are blocked.');
    }

    public function finalizeClosure(Project $project): RedirectResponse
    {
        $this->authorize('close', $project);

        $this->wallets->finalizeProjectClosure($project, request()->user());

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('status', 'Project closed. Remaining assigned balance released to unallocated funds.');
    }
}
