<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalaryPayment;
use App\Models\Project;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function index(Request $request): View
    {
        $projects = Project::query()
            ->with(['contractor', 'receipts', 'wallet'])
            ->orderByRaw(
                'CASE status WHEN ? THEN 0 WHEN ? THEN 1 ELSE 2 END',
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

        $today = now()->toDateString();
        $weekStart = now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd = now()->endOfWeek(Carbon::SUNDAY)->toDateString();

        $totalActiveEmployees = Employee::query()->active()->count();
        $presentToday = EmployeeAttendance::query()->whereDate('attendance_date', $today)->where('status', 'present')->count();
        $absentToday = EmployeeAttendance::query()->whereDate('attendance_date', $today)->where('status', 'absent')->count();

        $totalAdvances = EmployeeAdvance::query()->sum('amount');
        $totalRepayments = EmployeeSalaryPayment::query()->sum('advance_repayment');
        $outstandingAdvances = Money::sub(Money::normalize($totalAdvances ?? '0'), Money::normalize($totalRepayments ?? '0'));

        $salaryPaidThisWeek = EmployeeSalaryPayment::query()
            ->whereDate('payment_date', '>=', $weekStart)
            ->whereDate('payment_date', '<=', $weekEnd)
            ->sum('amount_paid');

        $pendingSalaryPayments = EmployeeSalaryPayment::query()
            ->where('status', 'partially_paid')
            ->count();

        return view('admin.dashboard', [
            'isOwner' => true,
            'businessBalance' => $this->wallets->businessBalance(),
            'unallocated' => $this->wallets->unallocatedFunds(),
            'ownerBalance' => $this->wallets->ownerBalance(),
            'projectRows' => $projectRows,
            'activeProjects' => $projects->where('status', ProjectStatus::Active)->count(),
            'closedProjects' => $projects->where('status', ProjectStatus::Closed)->count(),
            'recentTransactions' => $recentTransactions,
            'totalActiveEmployees' => $totalActiveEmployees,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'outstandingAdvances' => $outstandingAdvances,
            'salaryPaidThisWeek' => Money::normalize($salaryPaidThisWeek ?? '0'),
            'pendingSalaryPayments' => $pendingSalaryPayments,
        ]);
    }
}
