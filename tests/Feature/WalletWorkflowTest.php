<?php

namespace Tests\Feature;

use App\Exceptions\WalletException;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function service(): WalletService
    {
        return app(WalletService::class);
    }

    public function test_full_contract_workflow_from_zero_opening_balance(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor('Ramesh');
        $project = $this->makeProject($contractor, '1000000.00', 'Project A');

        $materials = $this->projectCategory('Materials');
        $transport = $this->projectCategory('Transport');
        $labour = $this->projectCategory('Labour');
        $ownerWithdrawal = $this->ownerCategory('Owner Withdrawal');

        // 1. Receive ₹5,00,000 for Project A.
        $service->receiveProjectPayment($project, '500000.00', '2026-10-01', 'First payment', $owner);

        // 2. Direct expenses: materials ₹35,000 + transport ₹15,000 = ₹50,000.
        $service->recordProjectExpense($project, '35000.00', $materials->id, '2026-10-02', 'Cement', $owner);
        $service->recordProjectExpense($project, '15000.00', $transport->id, '2026-10-03', 'Logistics', $owner);

        // 3. Transfer ₹50,000 to the contractor for labour.
        $service->transferToContractor(
            $project, $contractor, '50000.00', $labour->id, '2026-10-04', 'Labour payment', $owner
        );

        // 4. Close Project A and release the remaining assigned balance.
        $service->requestProjectClosure($project, $owner);
        $service->finalizeProjectClosure($project, $owner);

        // 5. Transfer ₹1,00,000 to the Owner Personal Wallet.
        $service->transferToOwner('100000.00', $ownerWithdrawal->id, '2026-10-06', null, $owner);

        $project->refresh();

        $this->assertSame('300000.00', $service->businessBalance());
        $this->assertSame('300000.00', $service->unallocatedFunds());
        $this->assertSame('0.00', $service->projectBalance($project));
        $this->assertSame('50000.00', $service->directProjectCosts($project));
        $this->assertSame('50000.00', $service->contractorTransfersTotal($project));
        $this->assertSame('100000.00', $service->totalProjectCosts($project));
        $this->assertSame('50000.00', $service->contractorBalance($contractor));
        $this->assertSame('100000.00', $service->ownerBalance());

        $this->assertTrue($project->isClosed());

        // Agreed amount is not money received; the received figure comes from receipts.
        $this->assertSame('500000.00', $project->totalReceived());
        $this->assertSame('500000.00', $project->remainingAgainstAgreed());
    }

    public function test_phased_receipts_track_total_received_separately_from_agreed_amount(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');

        $service->receiveProjectPayment($project, '200000.00', '2026-10-01', null, $owner);
        $service->receiveProjectPayment($project, '300000.00', '2026-10-02', null, $owner);

        $this->assertSame('500000.00', $project->totalReceived());
        $this->assertSame('500000.00', $project->remainingAgainstAgreed());
        $this->assertSame('500000.00', $service->businessBalance());
        $this->assertSame('500000.00', $service->projectBalance($project));
        $this->assertSame('0.00', $service->unallocatedFunds());
    }

    public function test_unallocated_income_increases_company_cash_only(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');

        $service->receiveUnallocatedIncome('100000.00', '2026-10-01', 'Other income', $owner);

        $this->assertSame('100000.00', $service->businessBalance());
        $this->assertSame('100000.00', $service->unallocatedFunds());
        $this->assertSame('0.00', $service->projectBalance($project));
    }

    public function test_project_receipt_keeps_money_out_of_unallocated_funds(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');

        $service->receiveProjectPayment($project, '500000.00', '2026-10-01', null, $owner);
        $service->receiveUnallocatedIncome('100000.00', '2026-10-02', null, $owner);

        // Company cash is 6,00,000 but 5,00,000 is assigned to the project.
        $this->assertSame('600000.00', $service->businessBalance());
        $this->assertSame('100000.00', $service->unallocatedFunds());
        $this->assertSame('500000.00', $service->projectBalance($project));
    }

    public function test_contractor_wallet_is_not_counted_as_free_company_money(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->projectCategory('Labour');

        $service->receiveProjectPayment($project, '500000.00', '2026-10-01', null, $owner);
        $service->transferToContractor($project, $contractor, '200000.00', $category->id, '2026-10-02', null, $owner);

        // Company cash dropped by the transfer; the contractor wallet tracks the
        // cumulative advance but is never counted as free company money.
        $this->assertSame('300000.00', $service->businessBalance());
        $this->assertSame('200000.00', $service->contractorBalance($contractor));
        $this->assertSame('300000.00', $service->projectBalance($project));
        $this->assertSame('0.00', $service->unallocatedFunds());
    }

    public function test_owner_return_moves_personal_money_back_into_company_cash(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $this->makeProject($contractor, '1000000.00');
        $category = $this->ownerCategory();

        // Start with ₹1,00,000 unallocated, then move ₹60,000 to the owner wallet.
        $service->receiveUnallocatedIncome('100000.00', '2026-10-01', null, $owner);
        $service->transferToOwner('60000.00', $category->id, '2026-10-02', null, $owner);

        $this->assertSame('40000.00', $service->businessBalance());
        $this->assertSame('60000.00', $service->ownerBalance());

        // Return ₹25,000 to the company.
        $service->returnOwnerFunds('25000.00', '2026-10-03', 'Return unused personal funds', $owner);

        $this->assertSame('65000.00', $service->businessBalance());
        $this->assertSame('35000.00', $service->ownerBalance());
        $this->assertSame('65000.00', $service->unallocatedFunds());
    }

    public function test_owner_return_is_blocked_when_personal_wallet_is_insufficient(): void
    {
        $service = $this->service();
        $owner = $this->owner();

        $this->expectException(WalletException::class);

        $service->returnOwnerFunds('1.00', '2026-10-01', null, $owner);
    }

    public function test_project_funding_reclassifies_unallocated_into_a_project(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');

        // ₹2,000 of unallocated company funds.
        $service->receiveUnallocatedIncome('2000.00', '2026-10-01', null, $owner);

        $this->assertSame('2000.00', $service->businessBalance());
        $this->assertSame('2000.00', $service->unallocatedFunds());

        // Assign ₹2,500? Only ₹2,000 is available, so this must fail.
        try {
            $service->fundProject($project, '2500.00', '2026-10-02', null, $owner);
            $this->fail('Funding beyond unallocated funds should have been rejected.');
        } catch (WalletException $e) {
            // expected
        }

        // Assign ₹1,500 instead.
        $service->fundProject($project, '1500.00', '2026-10-02', 'Seed project funds', $owner);

        // Company cash is unchanged; the ₹1,500 has simply moved from
        // unallocated to the project's assigned balance.
        $this->assertSame('2000.00', $service->businessBalance());
        $this->assertSame('1500.00', $service->projectBalance($project));
        $this->assertSame('500.00', $service->unallocatedFunds());
    }

    public function test_project_funding_is_blocked_for_a_non_active_project(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');

        $service->receiveUnallocatedIncome('5000.00', '2026-10-01', null, $owner);
        $service->requestProjectClosure($project, $owner);

        $this->expectException(WalletException::class);

        $service->fundProject($project, '1000.00', '2026-10-02', null, $owner);
    }

    public function test_project_funding_is_not_a_project_cost_and_leaves_company_cash_intact(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');

        $service->receiveUnallocatedIncome('100000.00', '2026-10-01', null, $owner);
        $service->fundProject($project, '40000.00', '2026-10-02', null, $owner);

        $this->assertSame('100000.00', $service->businessBalance());
        $this->assertSame('0.00', $service->totalProjectCosts($project));
        $this->assertSame('40000.00', $service->projectBalance($project));
        $this->assertSame('60000.00', $service->unallocatedFunds());
    }
}
