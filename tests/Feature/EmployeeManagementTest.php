<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalaryPayment;
use App\Models\EmployeeType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function employee(array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'employee_id' => 'EMP-2026-0001',
            'full_name' => 'Ramesh Patil',
            'phone_number' => '9022357293',
            'employee_type_id' => $this->type('Labour')->id,
            'weekly_salary_payment_day' => 'thursday',
            'joining_date' => '2026-10-01',
            'status' => 'active',
        ], $attributes));
    }

    private function type(string $name): EmployeeType
    {
        return EmployeeType::query()->where('name', $name)->firstOrFail();
    }

    private function attendanceOn(Employee $employee, string $date): EmployeeAttendance
    {
        return EmployeeAttendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $date)
            ->firstOrFail();
    }

    private function markWeek(Employee $employee, array $days)
    {
        return $this->actingAs($this->owner())->post('/admin/attendance', [
            'employee_id' => $employee->id,
            'reference_date' => '2026-10-28',
            'days' => $days,
        ]);
    }

    public function test_attendance_page_shows_the_selected_employees_week(): void
    {
        $owner = $this->owner();
        $employee = $this->employee();

        $this->actingAs($owner)->get('/admin/attendance')
            ->assertOk()
            ->assertSee('Select an employee to mark attendance');

        // 28 Oct 2026 is a Wednesday, so the Thursday-to-Thursday salary week
        // containing it runs 23 Oct (Fri) to 29 Oct (Thu).
        $this->actingAs($owner)
            ->get("/admin/attendance?employee_id={$employee->id}&reference_date=2026-10-28")
            ->assertOk()
            ->assertSee('23-10-2026')
            ->assertSee('29-10-2026')
            ->assertSee('name="days[2026-10-23][status]"', false)
            ->assertSee('name="days[2026-10-23][amount]"', false);
    }

    public function test_present_day_saves_the_entered_amount_and_can_be_edited(): void
    {
        $employee = $this->employee();

        $this->markWeek($employee, [
            '2026-10-23' => ['status' => 'present', 'amount' => '600'],
            '2026-10-24' => ['status' => 'present', 'amount' => '950'],
            '2026-10-25' => ['status' => 'absent', 'amount' => null],
            '2026-10-26' => ['status' => null, 'amount' => null],
        ])->assertSessionHas('status');

        $this->assertSame(3, EmployeeAttendance::query()->count());
        $this->assertSame('950.00', $this->attendanceOn($employee, '2026-10-24')->amount);
        $this->assertSame('0.00', $this->attendanceOn($employee, '2026-10-25')->amount);

        $this->markWeek($employee, ['2026-10-23' => ['status' => 'absent', 'amount' => null]]);

        $this->assertSame(3, EmployeeAttendance::query()->count());
        $this->assertSame('absent', $this->attendanceOn($employee, '2026-10-23')->status);
        $this->assertSame('0.00', $this->attendanceOn($employee, '2026-10-23')->amount);
    }

    public function test_present_day_needs_an_amount_and_other_days_still_save(): void
    {
        $employee = $this->employee();

        $this->markWeek($employee, [
            '2026-10-23' => ['status' => 'present', 'amount' => null],
            '2026-10-24' => ['status' => 'present', 'amount' => '700'],
        ])->assertSessionHas('error');

        $this->assertSame(1, EmployeeAttendance::query()->count());
        $this->assertSame('700.00', $this->attendanceOn($employee, '2026-10-24')->amount);
    }

    public function test_salary_is_the_total_of_the_days_amounts(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');

        $employee = $this->employee();

        $this->markWeek($employee, [
            '2026-10-23' => ['status' => 'present', 'amount' => '600'],
            '2026-10-24' => ['status' => 'present', 'amount' => '750.50'],
            '2026-10-25' => ['status' => 'absent', 'amount' => null],
        ]);

        $this->actingAs($this->owner())->post('/admin/pay-salary', [
            'employee_id' => $employee->id,
            'period_start' => '2026-10-23',
            'period_end' => '2026-10-29',
            'advance_repayment' => '0',
            'amount_paid' => '1350.50',
            'payment_date' => '2026-10-30',
        ])->assertRedirect();

        $payment = EmployeeSalaryPayment::query()->firstOrFail();

        $this->assertSame('1350.50', $payment->gross_salary);
        $this->assertSame('2.0', $payment->present_days);
        $this->assertSame('paid', $payment->status);
    }

    public function test_paid_week_attendance_is_locked(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');

        $employee = $this->employee();
        $this->markWeek($employee, ['2026-10-23' => ['status' => 'present', 'amount' => '600']]);

        $this->actingAs($this->owner())->post('/admin/pay-salary', [
            'employee_id' => $employee->id,
            'period_start' => '2026-10-23',
            'period_end' => '2026-10-29',
            'advance_repayment' => '0',
            'amount_paid' => '600',
            'payment_date' => '2026-10-30',
        ])->assertRedirect();

        $this->markWeek($employee, ['2026-10-23' => ['status' => 'present', 'amount' => '900']])
            ->assertSessionHas('error');

        $this->assertSame('600.00', $this->attendanceOn($employee, '2026-10-23')->amount);

        $this->actingAs($this->owner())
            ->get("/admin/attendance?employee_id={$employee->id}&reference_date=2026-10-23")
            ->assertSee('Salary paid, locked')
            ->assertDontSee('name="days[2026-10-23][amount]"', false);
    }

    public function test_attendance_before_joining_is_ignored(): void
    {
        $employee = $this->employee(['joining_date' => '2026-10-24']);

        $this->markWeek($employee, [
            '2026-10-23' => ['status' => 'present', 'amount' => '600'],
            '2026-10-24' => ['status' => 'present', 'amount' => '600'],
        ])->assertSessionHas('status');

        $this->assertSame(1, EmployeeAttendance::query()->count());
    }

    public function test_employee_pages_show_type_without_a_fixed_salary(): void
    {
        $owner = $this->owner();
        $employee = $this->employee();

        $this->actingAs($owner)->get('/admin/employees')->assertOk()->assertSee('Labour');
        $this->actingAs($owner)->get('/admin/employees/create')
            ->assertOk()
            ->assertSee('name="employee_type_id"', false)
            ->assertDontSee('name="daily_salary"', false);
        $this->actingAs($owner)->get("/admin/employees/{$employee->id}/edit")->assertOk();
        $this->actingAs($owner)->get("/admin/employees/{$employee->id}")->assertOk()->assertSee('Labour');
    }

    public function test_employee_is_created_with_a_generated_id(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->post('/admin/employees', [
                'full_name' => 'Somnath Mali',
                'phone_number' => '9022357293',
                'employee_type_id' => $this->type('Labour')->id,
                'weekly_salary_payment_day' => 'thursday',
                'joining_date' => '2026-10-22',
                'status' => 'active',
            ])
            ->assertRedirect();

        $employee = Employee::query()->where('full_name', 'Somnath Mali')->firstOrFail();

        $this->assertSame('EMP-'.now()->format('Y').'-0001', $employee->employee_id);
        $this->assertTrue($employee->isActive());
    }

    public function test_pay_salary_page_shows_the_last_completed_salary_week(): void
    {
        $owner = $this->owner();
        $employee = $this->employee();

        // Reference Wednesday 28 Oct 2026: the last completed Thursday week is
        // 16 Oct (Fri) to 22 Oct (Thu).
        $this->actingAs($owner)
            ->get("/admin/pay-salary?employee_id={$employee->id}&reference_date=2026-10-28")
            ->assertOk()
            ->assertSee('16-10-2026')
            ->assertSee('22-10-2026');
    }

    public function test_dashboard_shows_employee_summary_cards(): void
    {
        $owner = $this->owner();
        $employee = $this->employee();

        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => now()->toDateString(),
            'status' => 'present',
            'payable_days' => '1.0',
            'amount' => '600.00',
        ]);

        $absent = $this->employee([
            'employee_id' => 'EMP-2026-0002',
            'full_name' => 'Suresh Patil',
        ]);

        EmployeeAttendance::create([
            'employee_id' => $absent->id,
            'attendance_date' => now()->toDateString(),
            'status' => 'absent',
            'payable_days' => '0.0',
        ]);

        $this->actingAs($owner)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Total Active Employees')
            ->assertSee('Present Today')
            ->assertSee('Absent Today')
            ->assertSee('Outstanding Employee Advances')
            ->assertSee('Salary Paid This Week')
            ->assertSee('Pending Salary Payments')
            ->assertViewHas('totalActiveEmployees', 2)
            ->assertViewHas('presentToday', 1)
            ->assertViewHas('absentToday', 1);
    }

    public function test_pay_salary_page_shows_current_week_when_the_completed_week_has_no_attendance(): void
    {
        Carbon::setTestNow('2026-10-28 10:00:00');

        $owner = $this->owner();
        $employee = $this->employee();

        foreach (['2026-10-23', '2026-10-24', '2026-10-25', '2026-10-26', '2026-10-27', '2026-10-28'] as $date) {
            EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'attendance_date' => $date,
                'status' => 'present',
                'payable_days' => '1.0',
                'amount' => '600.00',
            ]);
        }

        // Reference 28 Oct: the completed Thursday week is 16-22 Oct (empty),
        // while the week in progress is 23-29 Oct (six marked days).
        $this->actingAs($owner)
            ->get("/admin/pay-salary?employee_id={$employee->id}&reference_date=2026-10-28")
            ->assertOk()
            ->assertSee('No attendance is marked for this salary week')
            ->assertDontSee('Record Payment')
            ->assertSee('23-10-2026')
            ->assertSee('29-10-2026')
            ->assertSee('₹3,600.00');
    }

    public function test_salary_without_attendance_cannot_be_recorded(): void
    {
        Carbon::setTestNow('2026-10-28 10:00:00');

        $owner = $this->owner();
        $employee = $this->employee();

        $this->actingAs($owner)
            ->post('/admin/pay-salary', [
                'employee_id' => $employee->id,
                'period_start' => '2026-10-16',
                'period_end' => '2026-10-22',
                'advance_repayment' => '0',
                'amount_paid' => '0',
                'payment_date' => '2026-10-28',
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, EmployeeSalaryPayment::query()->count());
    }

    public function test_employee_detail_paginates_attendance_advances_and_salary_history(): void
    {
        $owner = $this->owner();
        $employee = $this->employee();

        for ($i = 0; $i < 12; $i++) {
            EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'attendance_date' => now()->subDays($i)->toDateString(),
                'status' => 'present',
                'payable_days' => '1.0',
                'amount' => '600.00',
            ]);

            EmployeeAdvance::create([
                'employee_id' => $employee->id,
                'advance_date' => now()->subDays($i)->toDateString(),
                'amount' => '100.00',
            ]);

            EmployeeSalaryPayment::create([
                'employee_id' => $employee->id,
                'period_start' => now()->subWeeks($i + 1)->startOfWeek()->toDateString(),
                'period_end' => now()->subWeeks($i + 1)->endOfWeek()->toDateString(),
                'daily_salary' => '600.00',
                'gross_salary' => '600.00',
                'present_days' => '1.0',
                'half_days' => '0.0',
                'total_payable_days' => '1.0',
                'outstanding_advance_before' => '0.00',
                'advance_repayment' => '0.00',
                'net_salary_payable' => '600.00',
                'amount_paid' => $i === 0 ? '400.00' : '600.00',
                'remaining_salary_due' => $i === 0 ? '200.00' : '0.00',
                'remaining_advance_after' => '0.00',
                'status' => $i === 0 ? 'partially_paid' : 'paid',
                'payment_date' => now()->subWeeks($i + 1)->endOfWeek()->toDateString(),
            ]);
        }

        $this->actingAs($owner)
            ->get("/admin/employees/{$employee->id}")
            ->assertOk()
            ->assertViewHas('attendances', fn ($paginator) => $paginator->total() === 12 && $paginator->perPage() === 5)
            ->assertViewHas('advances', fn ($paginator) => $paginator->total() === 12 && $paginator->perPage() === 10)
            ->assertViewHas('salaryPayments', fn ($paginator) => $paginator->total() === 12 && $paginator->perPage() === 10)
            ->assertSee('attendance_page=2', false)
            ->assertSee('advance_page=2', false)
            ->assertSee('salary_page=2', false)
            ->assertSee('Amount to pay');

        $this->actingAs($owner)
            ->get("/admin/employees/{$employee->id}?attendance_page=2&advance_page=2&salary_page=2")
            ->assertOk();
    }

    public function test_partial_salary_payment_records_advance_repayment_once(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');

        $owner = $this->owner();
        $employee = $this->employee();

        EmployeeAdvance::create([
            'employee_id' => $employee->id,
            'advance_date' => '2026-10-01',
            'amount' => '10000.00',
            'notes' => 'Advance',
        ]);

        foreach (['2026-10-23', '2026-10-24', '2026-10-25', '2026-10-26', '2026-10-27'] as $date) {
            EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'attendance_date' => $date,
                'status' => 'present',
                'payable_days' => '1.0',
                'amount' => '600.00',
            ]);
        }

        $this->actingAs($owner)
            ->post('/admin/pay-salary', [
                'employee_id' => $employee->id,
                'period_start' => '2026-10-23',
                'period_end' => '2026-10-29',
                'advance_repayment' => '1000',
                'amount_paid' => '1500',
                'payment_date' => '2026-10-29',
            ])
            ->assertRedirect("/admin/employees/{$employee->id}");

        $payment = EmployeeSalaryPayment::query()->where('employee_id', $employee->id)->firstOrFail();

        $this->assertSame('3000.00', $payment->gross_salary);
        $this->assertSame('1000.00', $payment->advance_repayment);
        $this->assertSame('2000.00', $payment->net_salary_payable);
        $this->assertSame('1500.00', $payment->amount_paid);
        $this->assertSame('500.00', $payment->remaining_salary_due);
        $this->assertSame('9000.00', $payment->remaining_advance_after);
        $this->assertSame('partially_paid', $payment->status);

        $employee->refresh();
        $this->assertSame('10000.00', $employee->totalAdvances());
        $this->assertSame('1000.00', $employee->totalRepayments());
        $this->assertSame('9000.00', $employee->outstandingAdvance());
    }

    public function test_remaining_salary_can_be_paid_without_recording_the_advance_repayment_again(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');

        $owner = $this->owner();
        $employee = $this->employee();

        EmployeeAdvance::create([
            'employee_id' => $employee->id,
            'advance_date' => '2026-10-01',
            'amount' => '10000.00',
        ]);

        foreach (['2026-10-23', '2026-10-24', '2026-10-25', '2026-10-26', '2026-10-27'] as $date) {
            EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'attendance_date' => $date,
                'status' => 'present',
                'payable_days' => '1.0',
                'amount' => '600.00',
            ]);
        }

        $this->actingAs($owner)->post('/admin/pay-salary', [
            'employee_id' => $employee->id,
            'period_start' => '2026-10-23',
            'period_end' => '2026-10-29',
            'advance_repayment' => '1000',
            'amount_paid' => '1500',
            'payment_date' => '2026-10-29',
        ])->assertRedirect();

        $payment = EmployeeSalaryPayment::query()->where('employee_id', $employee->id)->firstOrFail();

        $this->actingAs($owner)
            ->post("/admin/pay-salary/{$payment->id}/complete", [
                'amount' => '500',
                'payment_date' => '2026-10-30',
            ])
            ->assertRedirect();

        $payment->refresh();
        $employee->refresh();

        $this->assertSame('2000.00', $payment->amount_paid);
        $this->assertSame('0.00', $payment->remaining_salary_due);
        $this->assertSame('paid', $payment->status);

        // The repayment stays at 1,000 and is never applied twice.
        $this->assertSame('1000.00', $payment->advance_repayment);
        $this->assertSame('9000.00', $employee->outstandingAdvance());
        $this->assertSame(1, EmployeeSalaryPayment::query()->count());
    }

    public function test_completing_a_salary_cannot_exceed_the_remaining_due(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');

        $owner = $this->owner();
        $employee = $this->employee();

        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-10-23',
            'status' => 'present',
            'payable_days' => '1.0',
            'amount' => '600.00',
        ]);

        $this->actingAs($owner)->post('/admin/pay-salary', [
            'employee_id' => $employee->id,
            'period_start' => '2026-10-23',
            'period_end' => '2026-10-29',
            'advance_repayment' => '0',
            'amount_paid' => '100',
            'payment_date' => '2026-10-29',
        ])->assertRedirect();

        $payment = EmployeeSalaryPayment::query()->where('employee_id', $employee->id)->firstOrFail();

        $this->actingAs($owner)
            ->post("/admin/pay-salary/{$payment->id}/complete", [
                'amount' => '700',
                'payment_date' => '2026-10-30',
            ])
            ->assertSessionHas('error');

        $payment->refresh();
        $this->assertSame('100.00', $payment->amount_paid);
        $this->assertSame('500.00', $payment->remaining_salary_due);
    }

    public function test_salary_period_cannot_be_paid_twice(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');

        $owner = $this->owner();
        $employee = $this->employee();

        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-10-23',
            'status' => 'present',
            'payable_days' => '1.0',
            'amount' => '600.00',
        ]);

        $payload = [
            'employee_id' => $employee->id,
            'period_start' => '2026-10-23',
            'period_end' => '2026-10-29',
            'advance_repayment' => '0',
            'amount_paid' => '600',
            'payment_date' => '2026-10-29',
        ];

        $this->actingAs($owner)->post('/admin/pay-salary', $payload)->assertRedirect();
        $this->actingAs($owner)->post('/admin/pay-salary', $payload)->assertSessionHas('error');

        $this->assertSame(1, EmployeeSalaryPayment::query()->where('employee_id', $employee->id)->count());
    }

    public function test_advance_repayment_cannot_exceed_outstanding_balance(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');

        $owner = $this->owner();
        $employee = $this->employee();

        EmployeeAdvance::create([
            'employee_id' => $employee->id,
            'advance_date' => '2026-10-01',
            'amount' => '1000.00',
        ]);

        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => '2026-10-23',
            'status' => 'present',
            'payable_days' => '1.0',
            'amount' => '600.00',
        ]);

        $this->actingAs($owner)
            ->post('/admin/pay-salary', [
                'employee_id' => $employee->id,
                'period_start' => '2026-10-23',
                'period_end' => '2026-10-29',
                'advance_repayment' => '2000',
                'amount_paid' => '0',
                'payment_date' => '2026-10-29',
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, EmployeeSalaryPayment::query()->count());
    }

    public function test_amount_paid_cannot_exceed_net_salary_payable(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');

        $owner = $this->owner();
        $employee = $this->employee();

        EmployeeAdvance::create([
            'employee_id' => $employee->id,
            'advance_date' => '2026-10-01',
            'amount' => '10000.00',
        ]);

        foreach (['2026-10-23', '2026-10-24', '2026-10-25', '2026-10-26', '2026-10-27'] as $date) {
            EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'attendance_date' => $date,
                'status' => 'present',
                'payable_days' => '1.0',
                'amount' => '600.00',
            ]);
        }

        $this->actingAs($owner)
            ->post('/admin/pay-salary', [
                'employee_id' => $employee->id,
                'period_start' => '2026-10-23',
                'period_end' => '2026-10-29',
                'advance_repayment' => '1000',
                'amount_paid' => '2500',
                'payment_date' => '2026-10-29',
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, EmployeeSalaryPayment::query()->count());
    }
}
