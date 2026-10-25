<?php

namespace Tests\Feature;

use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundMovementTest extends TestCase
{
    use RefreshDatabase;

    private function service(): WalletService
    {
        return app(WalletService::class);
    }

    public function test_owner_can_move_personal_funds_back_to_the_company(): void
    {
        $owner = $this->owner();
        $category = $this->ownerCategory();

        // Build a ₹1,000 unallocated pool, then move it to the personal wallet.
        $this->service()->receiveUnallocatedIncome('1000.00', '2026-10-01', null, $owner);

        $this->actingAs($owner)
            ->post('/admin/owner/transfer', [
                'amount' => '1000',
                'category_id' => $category->id,
                'transaction_date' => '2026-10-01',
            ])
            ->assertRedirect('/admin/owner');

        $this->actingAs($owner)
            ->post('/admin/owner/return', [
                'amount' => '400',
                'transaction_date' => '2026-10-02',
                'description' => 'Return',
            ])
            ->assertRedirect('/admin/owner');

        $this->assertSame('400.00', $this->service()->businessBalance());
        $this->assertSame('600.00', $this->service()->ownerBalance());
    }

    public function test_owner_can_fund_a_project_from_unallocated_funds(): void
    {
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '100000.00');
        $this->service()->projectWallet($project);

        // Build a ₹1,000 unallocated pool.
        $this->service()->receiveUnallocatedIncome('1000.00', '2026-10-01', null, $owner);

        $this->actingAs($owner)
            ->post("/admin/projects/{$project->id}/fund", [
                'amount' => '700',
                'transaction_date' => '2026-10-02',
                'description' => 'Seed funds',
            ])
            ->assertRedirect("/admin/projects/{$project->id}");

        $this->assertSame('1000.00', $this->service()->businessBalance());
        $this->assertSame('700.00', $this->service()->projectBalance($project));
        $this->assertSame('300.00', $this->service()->unallocatedFunds());
    }

    public function test_funding_above_unallocated_funds_returns_an_error(): void
    {
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '100000.00');
        $this->service()->projectWallet($project);

        $this->service()->receiveUnallocatedIncome('1000.00', '2026-10-01', null, $owner);

        $this->actingAs($owner)
            ->post("/admin/projects/{$project->id}/fund", [
                'amount' => '2500',
                'transaction_date' => '2026-10-02',
            ])
            ->assertSessionHas('error');

        $this->assertSame('0.00', $this->service()->projectBalance($project));
        $this->assertSame('1000.00', $this->service()->unallocatedFunds());
    }

    public function test_owner_can_give_money_to_a_contractor_through_the_ui(): void
    {
        $owner = $this->owner();
        $contractor = $this->contractor('Ramesh');
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->projectCategory('Labour');

        // Seed the project with ₹5,000.
        $this->actingAs($owner)->post("/admin/projects/{$project->id}/receipts", [
            'amount' => '5000',
            'transaction_date' => '2026-10-01',
        ]);

        $this->actingAs($owner)
            ->post("/admin/projects/{$project->id}/transfers", [
                'amount' => '2000',
                'category_id' => $category->id,
                'contractor_id' => $contractor->id,
                'transaction_date' => '2026-10-02',
                'description' => 'Advance for labour',
            ])
            ->assertRedirect("/admin/projects/{$project->id}");

        $this->assertSame('2000.00', $this->service()->contractorBalance($contractor));
        $this->assertSame('3000.00', $this->service()->projectBalance($project));
        $this->assertSame('3000.00', $this->service()->businessBalance());
    }
}
