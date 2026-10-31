<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TransactionType;
use App\Models\Contractor;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalaryPayment;
use App\Models\EmployeeType;
use App\Models\Project;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PaymentTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(string $mobile): TestResponse
    {
        return $this->post('/payment-tracking/verify', ['mobile' => $mobile]);
    }

    private function clientProject(string $name, string $mobile, string $agreed = '1000000.00'): Project
    {
        return Project::create([
            'name' => $name,
            'location' => 'Pune',
            'client_name' => 'Somnath Mali',
            'client_mobile' => $mobile,
            'client_mobile_normalized' => $mobile,
            'agreed_amount' => $agreed,
            'status' => ProjectStatus::Active,
        ]);
    }

    private function receipt(Project $project, string $amount, string $description = 'Payment received'): void
    {
        WalletTransaction::create([
            'type' => TransactionType::Receipt,
            'amount' => $amount,
            'transaction_date' => '2026-10-01',
            'description' => $description,
            'project_id' => $project->id,
        ]);
    }

    private function contractorTransfer(Contractor $contractor, Project $project, string $amount): void
    {
        WalletTransaction::create([
            'type' => TransactionType::ContractorTransfer,
            'amount' => $amount,
            'transaction_date' => '2026-10-02',
            'description' => 'Site payment',
            'project_id' => $project->id,
            'contractor_id' => $contractor->id,
        ]);
    }

    private function startLookup(string $category): void
    {
        $this->post('/payment-tracking/category', ['category' => $category])
            ->assertRedirect('/payment-tracking/verify');
    }

    public function test_home_page_shows_the_card_and_it_signs_in_with_the_number(): void
    {
        $this->clientProject('Alpha Villa', '9021113887');

        $this->get('/')
            ->assertOk()
            ->assertSee('id="track-payments"', false)
            ->assertSee('/payment-tracking/lookup', false)
            ->assertSee('No password or OTP needed.')
            ->assertSee('/payment-tracking"', false);

        $this->get('/mr')->assertOk()->assertSee('/mr/payment-tracking/lookup', false);

        $this->post('/payment-tracking/lookup', ['category' => 'client', 'mobile' => '9021113887'])
            ->assertRedirect('/payment-tracking/dashboard');

        $this->get('/payment-tracking/dashboard')->assertOk()->assertSee('Alpha Villa');
    }

    public function test_home_page_card_rejects_bad_input_and_unknown_numbers(): void
    {
        $home = url('/').'#track-payments';

        $this->post('/payment-tracking/lookup', ['category' => 'nobody', 'mobile' => '9021113887'])
            ->assertRedirect($home)
            ->assertSessionHasErrors('category');

        $this->post('/payment-tracking/lookup', ['category' => 'client', 'mobile' => '12345'])
            ->assertRedirect($home)
            ->assertSessionHasErrors('mobile');

        $this->post('/payment-tracking/lookup', ['category' => 'client', 'mobile' => '9999999999'])
            ->assertRedirect($home)
            ->assertSessionHas('tracking_error', __('site.payment_tracking.lookup_failed'));

        $this->get('/payment-tracking/dashboard')->assertRedirect('/payment-tracking');
    }

    public function test_unknown_mobile_shows_no_records(): void
    {
        $this->clientProject('Alpha Villa', '9021113887');

        $this->startLookup('client');

        $this->post('/payment-tracking/verify', ['mobile' => '9999999999'])
            ->assertRedirect('/payment-tracking/verify')
            ->assertSessionHas('tracking_error', __('site.payment_tracking.lookup_failed'));

        $this->get('/payment-tracking/dashboard')->assertRedirect('/payment-tracking');
    }

    public function test_duplicate_contractor_numbers_get_no_access(): void
    {
        $this->contractor('First Contractor', '9000000000');
        $this->contractor('Second Contractor', '9000000000');

        $this->startLookup('contractor');

        $this->post('/payment-tracking/verify', ['mobile' => '9000000000'])
            ->assertRedirect('/payment-tracking/verify')
            ->assertSessionHas('tracking_error', __('site.payment_tracking.lookup_failed'));
    }

    public function test_portal_renders_in_both_languages(): void
    {
        $this->get('/mr/payment-tracking')
            ->assertOk()
            ->assertSee('तुमचे पेमेंट तपासा')
            ->assertSee('ग्राहक');

        $this->get('/payment-tracking')
            ->assertOk()
            ->assertSee('Track Your Payments')
            ->assertSee('Client');
    }

    public function test_payment_pages_are_noindex_and_no_store(): void
    {
        foreach (['/payment-tracking', '/payment-tracking/verify'] as $url) {
            if (str_ends_with($url, 'verify')) {
                $this->startLookup('client');
            }

            $response = $this->get($url)->assertOk();

            $response->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');

            $cacheControl = (string) $response->headers->get('Cache-Control');
            $this->assertStringContainsString('no-store', $cacheControl);
            $this->assertStringContainsString('private', $cacheControl);

            $response->assertSee('noindex, nofollow', false);
        }
    }

    public function test_client_verification_shows_only_projects_linked_to_the_number(): void
    {
        $mine = $this->clientProject('Alpha Villa', '9021113887');
        $other = $this->clientProject('Other Villa', '9032221111');
        $this->receipt($mine, '50000', 'First instalment');
        $this->receipt($other, '75000', 'Someone else payment');

        $this->startLookup('client');

        $this->signIn('+91 90211 13887')
            ->assertRedirect('/payment-tracking/dashboard')
            ->assertSessionHas('payment_tracking.category', 'client');

        $this->get('/payment-tracking/dashboard')
            ->assertOk()
            ->assertSee('Somnath Mali')
            ->assertSee('+91 90211 13887')
            ->assertSee('Alpha Villa')
            ->assertSee('First instalment')
            ->assertSee('₹50,000.00')
            ->assertSee('₹10,00,000.00')
            ->assertSee('₹9,50,000.00')
            ->assertDontSee('Other Villa')
            ->assertDontSee('Someone else payment');
    }

    public function test_client_with_multiple_projects_sees_combined_totals(): void
    {
        $first = $this->clientProject('Alpha Villa', '9021113887', '1000000.00');
        $second = $this->clientProject('Beta House', '9021113887', '500000.00');
        $this->receipt($first, '50000');
        $this->receipt($second, '25000');

        $this->startLookup('client');
        $this->signIn('9021113887');

        $this->get('/payment-tracking/dashboard')
            ->assertOk()
            ->assertSee('₹15,00,000.00')
            ->assertSee('₹75,000.00')
            ->assertSee('₹14,25,000.00')
            ->assertSee('Alpha Villa')
            ->assertSee('Beta House');
    }

    public function test_client_cannot_switch_to_a_project_not_linked_to_their_number(): void
    {
        $mine = $this->clientProject('Alpha Villa', '9021113887');
        $unlinked = $this->clientProject('Secret Villa', '9032221111');

        $this->startLookup('client');
        $this->signIn('9021113887');

        $this->post('/payment-tracking/project', ['project_id' => $unlinked->id])
            ->assertRedirect('/payment-tracking/dashboard')
            ->assertSessionHas('tracking_error', __('site.payment_tracking.project_not_available'));

        $this->get('/payment-tracking/dashboard')
            ->assertOk()
            ->assertSee('Alpha Villa')
            ->assertDontSee('Secret Villa');

        $this->assertSame($mine->id, session('payment_tracking.selected_project_id'));
    }

    public function test_contractor_dashboard_shows_only_their_own_transfers(): void
    {
        $mine = $this->contractor('Ramesh Jadhav', '9000000001');
        $masonry = $this->contractorType('Masonry');
        $mine->update(['contractor_type_id' => $masonry->id]);
        $other = $this->contractor('Other Contractor', '9000000002');
        $project = $this->clientProject('Alpha Villa', '9021113887');

        $this->contractorTransfer($mine, $project, '12000');
        $this->contractorTransfer($other, $project, '8000');

        $this->startLookup('contractor');
        $this->signIn('9000000001')
            ->assertRedirect('/payment-tracking/dashboard');

        $this->get('/payment-tracking/dashboard')
            ->assertOk()
            ->assertSee('Ramesh Jadhav')
            ->assertSee('Masonry')
            ->assertSee('+91 90000 00001')
            ->assertSee('₹12,000.00')
            ->assertDontSee('₹8,000.00')
            ->assertDontSee('Other Contractor');
    }

    public function test_employee_dashboard_shows_salary_history_and_outstanding_advance(): void
    {
        $employee = Employee::create([
            'employee_id' => 'EMP-2026-0001',
            'full_name' => 'Ramesh Patil',
            'phone_number' => '9022357293',
            'weekly_salary_payment_day' => 'thursday',
            'joining_date' => '2026-10-01',
            'status' => 'active',
        ]);

        EmployeeAdvance::create([
            'employee_id' => $employee->id,
            'advance_date' => '2026-10-05',
            'amount' => '5000.00',
        ]);

        EmployeeSalaryPayment::create([
            'employee_id' => $employee->id,
            'period_start' => '2026-10-17',
            'period_end' => '2026-10-23',
            'daily_salary' => '600.00',
            'gross_salary' => '4200.00',
            'present_days' => 7,
            'half_days' => 0,
            'total_payable_days' => 7,
            'outstanding_advance_before' => '5000.00',
            'advance_repayment' => '2000.00',
            'net_salary_payable' => '2200.00',
            'amount_paid' => '2200.00',
            'remaining_salary_due' => '0.00',
            'remaining_advance_after' => '3000.00',
            'status' => 'paid',
            'payment_date' => '2026-10-24',
        ]);

        EmployeeSalaryPayment::create([
            'employee_id' => $employee->id,
            'period_start' => '2026-10-10',
            'period_end' => '2026-10-16',
            'daily_salary' => '600.00',
            'gross_salary' => '1500.00',
            'present_days' => 2.5,
            'half_days' => 0,
            'total_payable_days' => 2.5,
            'outstanding_advance_before' => '3000.00',
            'advance_repayment' => '0.00',
            'net_salary_payable' => '1500.00',
            'amount_paid' => '1000.00',
            'remaining_salary_due' => '500.00',
            'remaining_advance_after' => '3000.00',
            'status' => 'partially_paid',
            'payment_date' => '2026-10-17',
        ]);

        $this->startLookup('employee');
        $this->signIn('9022357293')
            ->assertRedirect('/payment-tracking/dashboard');

        $this->get('/payment-tracking/dashboard')
            ->assertOk()
            ->assertSee('Ramesh Patil')
            ->assertSee('EMP-2026-0001')
            ->assertSee('+91 90223 57293')
            ->assertSee('₹3,000.00')
            ->assertSee('₹500.00')
            ->assertSee('₹2,200.00')
            ->assertSee('Part paid');
    }

    public function test_employee_dashboard_shows_attendance_advances_and_profile(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $employee = Employee::create([
            'employee_id' => 'EMP-2026-0001',
            'full_name' => 'Ramesh Patil',
            'phone_number' => '9022357293',
            'employee_type_id' => EmployeeType::query()->where('name', 'Mistri')->value('id'),
            'weekly_salary_payment_day' => 'thursday',
            'joining_date' => '2026-10-01',
            'status' => 'active',
        ]);

        $other = Employee::create([
            'employee_id' => 'EMP-2026-0002',
            'full_name' => 'Other Worker',
            'phone_number' => '9022357294',
            'weekly_salary_payment_day' => 'thursday',
            'joining_date' => '2026-10-01',
            'status' => 'active',
        ]);

        // 23-29 Oct is the Thursday salary week containing 28 Oct; 20 Oct is
        // outside it, so it counts in the history but not this week's earnings.
        foreach ([['2026-10-20', 'present', '111.00'], ['2026-10-26', 'present', '750.00'], ['2026-10-27', 'absent', '0.00']] as [$date, $status, $amount]) {
            EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'attendance_date' => $date,
                'status' => $status,
                'payable_days' => $status === 'present' ? '1.0' : '0.0',
                'amount' => $amount,
            ]);
        }

        EmployeeAttendance::create([
            'employee_id' => $other->id,
            'attendance_date' => '2026-10-26',
            'status' => 'present',
            'payable_days' => '1.0',
            'amount' => '98765.00',
        ]);

        EmployeeAdvance::create([
            'employee_id' => $employee->id,
            'advance_date' => '2026-10-05',
            'amount' => '4321.00',
            'notes' => 'Private owner note',
        ]);

        $this->startLookup('employee');
        $this->signIn('9022357293');

        $this->get('/payment-tracking/dashboard')
            ->assertOk()
            ->assertSee('Mistri')
            ->assertSee('01 Oct 2026')
            ->assertSee('Thursday')
            ->assertSee('23 Oct')
            ->assertSee('Present: 1, Absent: 1.')
            ->assertSee('Earned so far: ₹750.00')
            ->assertSee('Not marked')
            ->assertSee('20 Oct 2026')
            ->assertSee('₹111.00')
            ->assertSee('₹4,321.00')
            ->assertDontSee('Private owner note')
            ->assertDontSee('₹98,765.00');
    }

    public function test_employee_dashboard_is_translated_to_marathi(): void
    {
        $this->assertSame(
            array_keys(__('site.payment_tracking.employee', [], 'en')),
            array_keys(__('site.payment_tracking.employee', [], 'mr'))
        );
    }

    public function test_verified_session_expires_after_the_configured_window(): void
    {
        $this->clientProject('Alpha Villa', '9021113887');

        $this->startLookup('client');
        $this->signIn('9021113887');

        $this->travel(31)->minutes();

        $this->get('/payment-tracking/dashboard')
            ->assertRedirect('/payment-tracking')
            ->assertSessionHas('tracking_error', __('site.payment_tracking.session_expired'))
            ->assertSessionMissing('payment_tracking');
    }

    public function test_lookup_is_rate_limited_per_ip_and_number(): void
    {
        $this->startLookup('client');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/payment-tracking/verify', ['mobile' => '9999999999'])
                ->assertRedirect();
        }

        $this->post('/payment-tracking/verify', ['mobile' => '9999999999'])
            ->assertStatus(429);
    }

    public function test_empty_states_are_shown_for_each_role(): void
    {
        $this->clientProject('Alpha Villa', '9021113887');
        $this->contractor('Ramesh Jadhav', '9000000001');
        Employee::create([
            'employee_id' => 'EMP-2026-0002',
            'full_name' => 'Empty Employee',
            'phone_number' => '9022357294',
            'weekly_salary_payment_day' => 'thursday',
            'joining_date' => '2026-10-01',
            'status' => 'active',
        ]);

        $this->startLookup('client');
        $this->signIn('9021113887');
        $this->get('/payment-tracking/dashboard')
            ->assertSee(__('site.payment_tracking.client.no_receipts'));
        $this->post('/payment-tracking/logout');

        $this->startLookup('contractor');
        $this->signIn('9000000001');
        $this->get('/payment-tracking/dashboard')
            ->assertSee(__('site.payment_tracking.contractor.no_records'))
            ->assertSee(__('site.payment_tracking.contractor.no_history'));
        $this->post('/payment-tracking/logout');

        $this->startLookup('employee');
        $this->signIn('9022357294');
        $this->get('/payment-tracking/dashboard')
            ->assertSee(__('site.payment_tracking.employee.no_records'))
            ->assertSee('₹0.00');
    }

    public function test_invalid_mobile_is_rejected_without_a_lookup(): void
    {
        $this->startLookup('client');

        $this->post('/payment-tracking/verify', ['mobile' => '12345'])
            ->assertSessionHasErrors('mobile')
            ->assertSessionMissing('payment_tracking.client_name');

        $this->get('/payment-tracking/verify')
            ->assertSee(__('site.payment_tracking.invalid_mobile'));
    }

    public function test_project_creation_stores_the_client_number_in_normalized_form(): void
    {
        $this->actingAs($this->owner())
            ->post('/admin/projects', [
                'name' => 'New Villa',
                'location' => 'Pune',
                'client_name' => 'Somnath Mali',
                'client_mobile' => '+91 90211 13887',
                'agreed_amount' => '1000000',
            ])
            ->assertRedirect();

        $project = Project::query()->where('name', 'New Villa')->firstOrFail();

        $this->assertSame('9021113887', $project->client_mobile_normalized);
        $this->assertSame('+91 90211 13887', $project->client_mobile);
    }
}
