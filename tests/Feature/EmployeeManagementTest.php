<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalaryPayment;
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
            'daily_salary' => '600.00',
            'weekly_salary_payment_day' => 'thursday',
            'joining_date' => '2026-10-01',
            'status' => 'active',
        ], $attributes));
    }

    public function test_employee_is_created_with_a_generated_id(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->post('/admin/employees', [
                'full_name' => 'Somnath Mali',
                'phone_number' => '9022357293',
                'daily_salary' => '800',
                'weekly_salary_payment_day' => 'thursday',
                'joining_date' => '2026-10-22',
                'status' => 'active',
            ])
            ->assertRedirect();

        $employee = Employee::query()->where('full_name', 'Somnath Mali')->firstOrFail();

        $this->assertSame('EMP-'.now()->format('Y').'-0001', $employee->employee_id);
        $this->assertSame('800.00', $employee->daily_salary);
        $this->assertTrue($employee->isActive());
    }

    public function test_weekly_attendance_chart_uses_the_week_containing_the_reference_date(): void
    {
        $owner = $this->owner();
        $employee = $this->employee();

        // 28 Oct 2026 is a Wednesday, so the Thursday-to-Thursday salary week
        // containing it runs 23 Oct (Fri) to 29 Oct (Thu).
        $this->actingAs($owner)
            ->get("/admin/attendance/weekly?employee_id={$employee->id}&reference_date=2026-10-28")
            ->assertOk()
            ->assertSee('23-10-2026')
            ->assertSee('29-10-2026');
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

    public function test_weekly_attendance_is_saved_and_editable_without_duplicates(): void
    {
        $owner = $this->owner();
        $employee = $this->employee();

        $this->actingAs($owner)
            ->post('/admin/attendance', [
                'weekly' => [
                    $employee->id => [
                        '2026-10-23' => 'present',
                        '2026-10-24' => 'half_day',
                    ],
                ],
                'weekly_employee_id' => $employee->id,
                'weekly_reference_date' => '2026-10-28',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-10-23 00:00:00',
            'status' => 'present',
            'payable_days' => '1.0',
        ]);
        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-10-24 00:00:00',
            'status' => 'half_day',
            'payable_days' => '0.5',
        ]);

        // Saving the same day again updates it instead of creating a second row.
        $this->actingAs($owner)
            ->post('/admin/attendance', [
                'weekly' => [
                    $employee->id => [
                        '2026-10-23' => 'absent',
                    ],
                ],
                'weekly_employee_id' => $employee->id,
                'weekly_reference_date' => '2026-10-28',
            ])
            ->assertRedirect();

        $this->assertSame(2, EmployeeAttendance::query()->where('employee_id', $employee->id)->count());
        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-10-23 00:00:00',
            'status' => 'absent',
            'payable_days' => '0.0',
        ]);
    }

    public function test_weekly_attendance_ignores_days_before_joining(): void
    {
        $owner = $this->owner();
        $employee = $this->employee(['joining_date' => '2026-10-22']);

        $this->actingAs($owner)
            ->post('/admin/attendance', [
                'weekly' => [
                    $employee->id => [
                        '2026-10-20' => 'present',
                        '2026-10-22' => 'present',
                    ],
                ],
                'weekly_employee_id' => $employee->id,
                'weekly_reference_date' => '2026-10-22',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('employee_attendances', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-10-20 00:00:00',
        ]);
        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-10-22 00:00:00',
            'status' => 'present',
        ]);
    }

    public function test_daily_attendance_ignores_days_before_joining(): void
    {
        $owner = $this->owner();
        $employee = $this->employee(['joining_date' => '2026-10-22']);

        $this->actingAs($owner)
            ->post('/admin/attendance', [
                'attendance_date' => '2026-10-20',
                'attendance' => [
                    ['employee_id' => $employee->id, 'status' => 'present'],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('employee_attendances', [
            'employee_id' => $employee->id,
        ]);
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
