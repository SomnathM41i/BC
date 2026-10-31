<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeSalaryRequest;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalaryPayment;
use App\Support\Money;
use App\Support\WorkLevel;
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
        $summary = null;

        if ($selectedEmployeeId) {
            $employee = Employee::find($selectedEmployeeId);
            if ($employee) {
                $summary = $this->calculateSummary($employee, $referenceDate);
            }
        }

        return view('admin.pay-salary.create', [
            'employees' => $employees,
            'selectedEmployeeId' => $selectedEmployeeId,
            'referenceDate' => $referenceDate,
            'employee' => $employee,
            'summary' => $summary,
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

    /**
     * Salary can be paid on any day, so a payment covers every marked day up to
     * the chosen date that no earlier payment covers. Days after it stay open.
     */
    protected function calculateSummary(Employee $employee, string $upTo): array
    {
        $payments = $employee->salaryPayments()->get();
        $isPaid = fn (Carbon $date) => $payments->contains(
            fn (EmployeeSalaryPayment $payment) => $date->between($payment->period_start, $payment->period_end)
        );

        $attendances = EmployeeAttendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', '<=', $upTo)
            ->orderBy('attendance_date')
            ->get()
            ->reject(fn (EmployeeAttendance $att) => $isPaid($att->attendance_date))
            ->values();

        $periodStart = $attendances->first()?->attendance_date->toDateString() ?? $upTo;
        $periodEnd = $attendances->last()?->attendance_date->toDateString() ?? $upTo;

        $present = 0;
        $half = 0;
        $payable = '0.0';
        $gross = '0.00';
        foreach ($attendances as $att) {
            if ($att->work_level === 'half_day') {
                $half++;
            } elseif ($att->status === 'present') {
                $present++;
            }
            $payable = bcadd($payable, (string) $att->payable_days, 1);
            $gross = Money::add($gross, $att->amount);
        }

        // Paying locks everything between the first and last day, so days left
        // unmarked in that range could not be marked afterwards.
        $marked = $attendances->map(fn (EmployeeAttendance $att) => $att->attendance_date->toDateString())->all();
        $unmarked = 0;
        for ($date = Carbon::parse($periodStart); $date->lte($periodEnd); $date->addDay()) {
            if (! in_array($date->toDateString(), $marked, true) && ! $isPaid($date)) {
                $unmarked++;
            }
        }

        $outstanding = $employee->outstandingAdvance();

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'present_days' => $present,
            'half_days' => $half,
            'total_payable_days' => $payable,
            'rate_breakdown' => WorkLevel::breakdownFor($attendances),
            'gross_salary' => $gross,
            'outstanding_advance' => $outstanding,
            'net_salary_payable_before_repayment' => $gross, // repayment applied on payment
            'unmarked_days' => $unmarked,
        ];
    }

    public function store(StoreEmployeeSalaryRequest $request): RedirectResponse
    {
        $employee = Employee::findOrFail($request->validated('employee_id'));

        $upTo = $request->validated('period_end');

        try {
            $payment = DB::transaction(function () use ($request, $employee, $upTo) {
                // Lock the employee row so two submissions cannot both pay the
                // same days. The second finds nothing left and is refused below.
                Employee::query()->whereKey($employee->id)->lockForUpdate()->first();

                $summary = $this->calculateSummary($employee, $upTo);

                // Blocking a zero total stops an empty payment being recorded.
                if (Money::isZero($summary['gross_salary'])) {
                    throw new \RuntimeException('There are no unpaid days up to '.Carbon::parse($upTo)->format('d-m-Y').'. Mark attendance before recording salary.');
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
                    'period_start' => $summary['period_start'],
                    'period_end' => $summary['period_end'],
                    'daily_salary' => '0.00',
                    'gross_salary' => $gross,
                    'present_days' => $summary['present_days'],
                    'half_days' => $summary['half_days'],
                    'total_payable_days' => $summary['total_payable_days'],
                    'rate_breakdown' => $summary['rate_breakdown'],
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
