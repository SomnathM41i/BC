<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeSalaryRequest;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalaryPayment;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeSalaryController extends Controller
{
    public function create(Request $request): View
    {
        $employees = Employee::query()->orderBy('full_name')->get();
        $selectedEmployeeId = $request->input('employee_id');
        $referenceDate = $request->input('reference_date', now()->toDateString());

        $employee = null;
        $period = null;
        $summary = null;
        $currentPeriod = null;
        $currentSummary = null;
        $currentWeekEnded = true;

        if ($selectedEmployeeId) {
            $employee = Employee::find($selectedEmployeeId);
            if ($employee) {
                $period = $employee->getWeeklyPeriod($referenceDate);
                $summary = $this->calculateSummary($employee, $period['start_date'], $period['end_date']);

                // The attendance chart edits the week containing today. Show that
                // week too, so days marked there are visible even though only the
                // last completed week can actually be paid.
                $currentPeriod = $employee->getWeekContaining($referenceDate);
                if ($currentPeriod['start_date'] !== $period['start_date']) {
                    $currentSummary = $this->calculateSummary($employee, $currentPeriod['start_date'], $currentPeriod['end_date']);
                    $currentWeekEnded = Carbon::parse($currentPeriod['end_date'])->lte(now()->endOfDay());
                }
            }
        }

        return view('admin.pay-salary.create', [
            'employees' => $employees,
            'selectedEmployeeId' => $selectedEmployeeId,
            'referenceDate' => $referenceDate,
            'employee' => $employee,
            'period' => $period,
            'summary' => $summary,
            'currentPeriod' => $currentPeriod,
            'currentSummary' => $currentSummary,
            'currentWeekEnded' => $currentWeekEnded,
        ]);
    }

    public function calculate(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'reference_date' => ['required', 'date'],
        ]);

        return redirect()->route('admin.pay-salary.create', [
            'employee_id' => $request->input('employee_id'),
            'reference_date' => $request->input('reference_date'),
        ]);
    }

    protected function calculateSummary(Employee $employee, string $startDate, string $endDate): array
    {
        // Prevent paying already-paid period twice
        $existing = EmployeeSalaryPayment::query()
            ->where('employee_id', $employee->id)
            ->where('period_start', $startDate)
            ->where('period_end', $endDate)
            ->first();

        $attendances = EmployeeAttendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', '>=', $startDate)
            ->whereDate('attendance_date', '<=', $endDate)
            ->get();

        $present = 0;
        $half = 0;
        $payable = '0.0';
        $gross = '0.00';
        foreach ($attendances as $att) {
            if ($att->status === 'present') {
                $present++;
            } elseif ($att->status === 'half_day') {
                $half++;
            }
            $payable = bcadd($payable, (string) $att->payable_days, 1);
            $gross = Money::add($gross, $att->amount);
        }
        $outstanding = $employee->outstandingAdvance();
        $netPayable = $gross; // before repayment selection; repayment applied on payment

        return [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'present_days' => $present,
            'half_days' => $half,
            'total_payable_days' => $payable,
            'gross_salary' => $gross,
            'outstanding_advance' => $outstanding,
            'net_salary_payable_before_repayment' => $netPayable,
            'already_paid' => (bool) $existing,
            'existing_payment' => $existing,
        ];
    }

    public function store(StoreEmployeeSalaryRequest $request): RedirectResponse
    {
        $employee = Employee::findOrFail($request->validated('employee_id'));

        // Derive the period from the employee's payment day instead of trusting
        // the hidden fields, so a week can never be split or shifted.
        $expected = $employee->getWeeklyPeriod($request->validated('period_end'));
        $periodStart = $expected['start_date'];
        $periodEnd = $expected['end_date'];

        if (Carbon::parse($periodEnd)->gt(now()->endOfDay())) {
            return back()->with('error', 'This salary week has not ended yet.');
        }

        try {
            $payment = DB::transaction(function () use ($request, $employee, $periodStart, $periodEnd) {
                // Lock the employee row so two submissions cannot both pass the
                // "already paid" check and create duplicate payments.
                Employee::query()->whereKey($employee->id)->lockForUpdate()->first();

                $duplicate = EmployeeSalaryPayment::query()
                    ->where('employee_id', $employee->id)
                    ->where('period_start', $periodStart)
                    ->where('period_end', $periodEnd)
                    ->exists();

                if ($duplicate) {
                    throw new \RuntimeException('This salary period has already been paid.');
                }

                $summary = $this->calculateSummary($employee, $periodStart, $periodEnd);

                // A week with no attendance has nothing to pay. Blocking it here
                // stops a ₹0 payment from locking the period as already paid.
                if (Money::isZero($summary['gross_salary'])) {
                    throw new \RuntimeException('No attendance is marked for '.Carbon::parse($periodStart)->format('d-m-Y').' to '.Carbon::parse($periodEnd)->format('d-m-Y').'. Mark attendance before recording salary.');
                }

                $gross = $summary['gross_salary'];
                $outstandingBefore = $employee->outstandingAdvance();

                $repayment = Money::normalize($request->validated('advance_repayment'));
                if (Money::gt($repayment, $outstandingBefore)) {
                    throw new \RuntimeException('Advance repayment cannot exceed the outstanding advance of '.Money::inr($outstandingBefore).'.');
                }

                $netPayable = Money::sub($gross, $repayment);
                if (Money::lt($netPayable, '0')) {
                    $netPayable = '0.00';
                }

                $amountPaid = Money::normalize($request->validated('amount_paid'));
                if (Money::gt($amountPaid, $netPayable)) {
                    throw new \RuntimeException('Amount paid cannot exceed the net salary payable of '.Money::inr($netPayable).'.');
                }

                $remainingSalaryDue = Money::sub($netPayable, $amountPaid);
                $status = Money::isZero($remainingSalaryDue) ? 'paid' : 'partially_paid';

                // The advance repayment is stored once on this salary payment.
                // Outstanding advance is always total advances minus the sum of
                // these repayment columns, so nothing can be counted twice.
                return EmployeeSalaryPayment::create([
                    'employee_id' => $employee->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'daily_salary' => '0.00',
                    'gross_salary' => $gross,
                    'present_days' => $summary['present_days'],
                    'half_days' => $summary['half_days'],
                    'total_payable_days' => $summary['total_payable_days'],
                    'outstanding_advance_before' => $outstandingBefore,
                    'advance_repayment' => $repayment,
                    'net_salary_payable' => $netPayable,
                    'amount_paid' => $amountPaid,
                    'remaining_salary_due' => $remainingSalaryDue,
                    'remaining_advance_after' => Money::sub($outstandingBefore, $repayment),
                    'status' => $status,
                    'payment_date' => $request->validated('payment_date'),
                    'notes' => $request->validated('notes'),
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('status', 'Salary payment recorded.');
    }

    /**
     * Add the rest of a partially paid salary without creating a second
     * payment for the same period. The advance repayment is not touched, so it
     * can never be recorded twice.
     */
    public function complete(Request $request, EmployeeSalaryPayment $salaryPayment): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($salaryPayment, $data) {
                $payment = EmployeeSalaryPayment::query()
                    ->whereKey($salaryPayment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! Money::gt($payment->remaining_salary_due, '0')) {
                    throw new \RuntimeException('This salary payment is already fully paid.');
                }

                $amount = Money::normalize($data['amount']);
                if (Money::gt($amount, $payment->remaining_salary_due)) {
                    throw new \RuntimeException('Amount cannot exceed the remaining salary due of '.Money::inr($payment->remaining_salary_due).'.');
                }

                $remaining = Money::sub($payment->remaining_salary_due, $amount);

                $payment->update([
                    'amount_paid' => Money::add($payment->amount_paid, $amount),
                    'remaining_salary_due' => $remaining,
                    'status' => Money::isZero($remaining) ? 'paid' : 'partially_paid',
                    'notes' => $data['notes'] ?? $payment->notes,
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', 'Salary payment updated.');
    }
}
