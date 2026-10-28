<?php

namespace Tests\Feature;

use App\Models\Contractor;
use App\Models\Project;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exercises the whole HTTP path: routes, form requests, authorization,
 * controllers, WalletService and Blade views.
 */
class HttpWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_run_the_full_workflow_through_the_http_layer(): void
    {
        $wallets = app(WalletService::class);
        $owner = $this->owner();
        $contractor = $this->contractor('Ramesh');

        $materials = $this->projectCategory('Materials');
        $transport = $this->projectCategory('Transport');
        $labour = $this->projectCategory('Labour');
        $ownerWithdrawal = $this->ownerCategory('Owner Withdrawal');

        $this->actingAs($owner);

        // Create the project.
        $this->post('/admin/projects', [
            'name' => 'Project A',
            'location' => 'Pune',
            'client_name' => 'Somnath Mali',
            'client_mobile' => '9021113887',
            'agreed_amount' => '1000000',
            'contractor_id' => $contractor->id,
        ])->assertRedirect();

        $project = Project::query()->where('name', 'Project A')->firstOrFail();
        $this->assertSame('0.00', $wallets->projectBalance($project));

        // Receive ₹5,00,000.
        $this->post("/admin/projects/{$project->id}/receipts", [
            'amount' => '500000',
            'transaction_date' => '2026-10-01',
            'description' => 'First payment',
        ])->assertRedirect("/admin/projects/{$project->id}");

        // Direct expenses ₹50,000.
        $this->post("/admin/projects/{$project->id}/expenses", [
            'amount' => '35000',
            'category_id' => $materials->id,
            'transaction_date' => '2026-10-02',
        ])->assertRedirect("/admin/projects/{$project->id}");

        $this->post("/admin/projects/{$project->id}/expenses", [
            'amount' => '15000',
            'category_id' => $transport->id,
            'transaction_date' => '2026-10-03',
        ])->assertRedirect("/admin/projects/{$project->id}");

        // Transfer ₹50,000 to the contractor.
        $this->post("/admin/projects/{$project->id}/transfers", [
            'amount' => '50000',
            'category_id' => $labour->id,
            'contractor_id' => $contractor->id,
            'transaction_date' => '2026-10-04',
            'description' => 'Labour payment',
        ])->assertRedirect("/admin/projects/{$project->id}");

        // Close the project.
        $this->post("/admin/projects/{$project->id}/request-closure")->assertRedirect("/admin/projects/{$project->id}");
        $this->post("/admin/projects/{$project->id}/finalize-closure")->assertRedirect("/admin/projects/{$project->id}");

        // Transfer ₹1,00,000 to the owner personal wallet.
        $this->post('/admin/owner/transfer', [
            'amount' => '100000',
            'category_id' => $ownerWithdrawal->id,
            'transaction_date' => '2026-10-06',
        ])->assertRedirect('/admin/owner');

        $project->refresh();

        $this->assertSame('300000.00', $wallets->businessBalance());
        $this->assertSame('300000.00', $wallets->unallocatedFunds());
        $this->assertSame('0.00', $wallets->projectBalance($project));
        $this->assertSame('50000.00', $wallets->directProjectCosts($project));
        $this->assertSame('50000.00', $wallets->contractorTransfersTotal($project));
        $this->assertSame('100000.00', $wallets->totalProjectCosts($project));
        $this->assertSame('50000.00', $wallets->contractorBalance($contractor));
        $this->assertSame('100000.00', $wallets->ownerBalance());
        $this->assertTrue($project->isClosed());

        // Dashboard renders the final figures.
        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('₹3,00,000.00');
    }

    public function test_owner_can_create_a_contractor_through_the_http_layer(): void
    {
        $owner = $this->owner();
        $type = $this->contractorType('Plumbing');

        $this->actingAs($owner);

        $this->post('/admin/contractors', [
            'name' => 'Ganesh Patil',
            'phone' => '+91 9000000000',
            'contractor_type_id' => $type->id,
        ])->assertRedirect('/admin/contractors');

        $contractor = Contractor::query()->where('name', 'Ganesh Patil')->firstOrFail();

        $this->get('/admin/contractors')
            ->assertOk()
            ->assertSee('Ganesh Patil')
            ->assertSee('Plumbing');

        $this->assertNotNull(
            Wallet::query()->where('contractor_id', $contractor->id)->first()
        );
    }
}
