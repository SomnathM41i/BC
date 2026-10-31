<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalaryPayment;
use App\Models\EmployeeType;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * Demo employees for reviewing the employee module locally.
 *
 * Run explicitly: php artisan db:seed --class=DemoEmployeeSeeder
 *
 * Only runs in the local environment. It resets the history of the two demo
 * employees (EMP-2026-0002 / EMP-2026-0003) on every run, so it is safe to
 * repeat while reviewing. The real employee records are never touched.
 */
class DemoEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('DemoEmployeeSeeder is only for local review.');
        }

        $today = now()->startOfDay();

        $ramesh = $this->makeEmployee([
            'employee_id' => 'EMP-2026-0002',
            'full_name' => 'Ramesh Patil',
            'phone_number' => '9876543210',
            'employee_type_id' => EmployeeType::query()->where('name', 'Labour')->value('id'),
            'weekly_salary_payment_day' => 'saturday',
            'joining_date' => '2026-09-15',
            'status' => 'active',
        ]);

        $suresh = $this->makeEmployee([
            'employee_id' => 'EMP-2026-0003',
            'full_name' => 'Suresh Jadhav',
            'phone_number' => '9823456789',
            'employee_type_id' => EmployeeType::query()->where('name', 'Mistri')->value('id'),
            'weekly_salary_payment_day' => 'wednesday',
            'joining_date' => '2026-10-01',
            'status' => 'active',
        ]);

        // Reset only demo-owned history so repeated runs stay deterministic.
        foreach ([$ramesh, $suresh] as $employee) {
            $employee->salaryPayments()->delete();
            $employee->advances()->delete();
            $employee->attendances()->delete();
        }

        EmployeeAdvance::create([
            'employee_id' => $ramesh->id,
            'advance_date' => '2026-10-05',
            'amount' => '5000.00',
            'notes' => 'DEMO — festival advance',
        ]);

        EmployeeAdvance::create([
            'employee_id' => $suresh->id,
            'advance_date' => '2026-10-12',
            'amount' => '3000.00',
            'notes' => 'DEMO — medical advance',
        ]);

        $this->seedAttendance($ramesh, $today, fn (Carbon $date) => match ($date->dayOfWeek) {
            Carbon::SATURDAY => 'absent',
            default => 'present',
        });

        $this->seedAttendance($suresh, $today, fn (Carbon $date) => match ($date->dayOfWeek) {
            Carbon::SUNDAY => 'absent',
            default => 'present',
        });

        // Keep one employee absent today so both dashboard counts are visible.
        $this->saveAttendance($suresh, $today->toDateString(), 'absent');

        // Ramesh: completed week paid in full, repaying part of his advance.
        $this->seedSalary($ramesh, $today, '2000.00');

        // Suresh: completed week paid partially, leaving a Pay Balance action.
        $this->seedSalary($suresh, $today, '1000.00', 0.6);

        $this->command?->info('Demo employees ready: Ramesh Patil (EMP-2026-0002), Suresh Jadhav (EMP-2026-0003).');
    }

    private function makeEmployee(array $attributes): Employee
    {
        return Employee::query()->updateOrCreate(
            ['employee_id' => $attributes['employee_id']],
            $attributes
        );
    }

    private function seedAttendance(Employee $employee, Carbon $today, callable $statusFor): void
    {
        for ($date = $today->copy()->subDays(20); $date->lte($today); $date->addDay()) {
            if ($date->lt($employee->joining_date)) {
                continue;
            }

            $this->saveAttendance($employee, $date->toDateString(), $statusFor($date->copy()));
        }
    }

    private function saveAttendance(Employee $employee, string $date, string $status): void
    {
        $employee->attendances()->whereDate('attendance_date', $date)->delete();

        $employee->attendances()->create([
            'attendance_date' => $date,
            'status' => $status,
            'payable_days' => $status === 'present' ? '1.0' : '0.0',
            'amount' => $status === 'present' ? ($employee->id % 2 ? '700.00' : '650.00') : '0.00',
        ]);
    }

    private function seedSalary(Employee $employee, Carbon $today, string $repayment, ?float $paidRatio = null): void
    {
        $period = $employee->getWeeklyPeriod($today);

        $attendances = $employee->attendances()
            ->whereDate('attendance_date', '>=', $period['start_date'])
            ->whereDate('attendance_date', '<=', $period['end_date'])
            ->get();

        if ($attendances->isEmpty()) {
            return;
        }

        $payable = $attendances->sum(fn (EmployeeAttendance $attendance) => (float) $attendance->payable_days);
        $gross = Money::sum($attendances, fn (EmployeeAttendance $attendance) => $attendance->amount);

        $outstandingBefore = $employee->outstandingAdvance();
        $repayment = Money::normalize($repayment);
        if (Money::gt($repayment, $outstandingBefore)) {
            $repayment = $outstandingBefore;
        }

        $net = Money::sub($gross, $repayment);

        $paid = $paidRatio === null
            ? $net
            : Money::normalize(bcmul($net, (string) $paidRatio, 2));

        if (Money::gt($paid, $net)) {
            $paid = $net;
        }

        $remaining = Money::sub($net, $paid);

        EmployeeSalaryPayment::create([
            'employee_id' => $employee->id,
            'period_start' => $period['start_date'],
            'period_end' => $period['end_date'],
            'daily_salary' => '0.00',
            'gross_salary' => $gross,
            'present_days' => $attendances->where('status', 'present')->count(),
            'half_days' => $attendances->where('status', 'half_day')->count(),
            'total_payable_days' => number_format($payable, 1, '.', ''),
            'outstanding_advance_before' => $outstandingBefore,
            'advance_repayment' => $repayment,
            'net_salary_payable' => $net,
            'amount_paid' => $paid,
            'remaining_salary_due' => $remaining,
            'remaining_advance_after' => Money::sub($outstandingBefore, $repayment),
            'status' => Money::isZero($remaining) ? 'paid' : 'partially_paid',
            'payment_date' => $today->toDateString(),
            'notes' => 'DEMO — seeded salary',
        ]);
    }
}
