<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function index(Request $request): View
    {
        $projects = Project::query()
            ->with(['contractor', 'receipts', 'wallet'])
            ->orderByRaw(
                "CASE status WHEN ? THEN 0 WHEN ? THEN 1 ELSE 2 END",
                [ProjectStatus::Active->value, ProjectStatus::ClosurePending->value]
            )->orderBy('name')
            ->get();

        $projectRows = $projects->map(fn (Project $project) => [
            'project' => $project,
            'report' => $this->wallets->projectReport($project),
        ]);

        $recentTransactions = WalletTransaction::query()
            ->with(['project', 'category', 'contractor'])
            ->recentFirst()
            ->limit(15)
            ->get();

        return view('dashboard', [
            'isOwner' => true,
            'businessBalance' => $this->wallets->businessBalance(),
            'unallocated' => $this->wallets->unallocatedFunds(),
            'ownerBalance' => $this->wallets->ownerBalance(),
            'projectRows' => $projectRows,
            'activeProjects' => $projects->where('status', ProjectStatus::Active)->count(),
            'closedProjects' => $projects->where('status', ProjectStatus::Closed)->count(),
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
