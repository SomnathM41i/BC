<?php

namespace Tests\Feature;

use App\Exceptions\WalletException;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletValidationTest extends TestCase
{
    use RefreshDatabase;

    private function service(): WalletService
    {
        return app(WalletService::class);
    }

    public function test_project_expense_overdraws_the_project_and_company_then_receipts_cover_it(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->projectCategory();

        $service->recordProjectExpense($project, '1000.00', $category->id, '2026-10-01', null, $owner);

        $this->assertSame('-1000.00', $service->projectBalance($project));
        $this->assertSame('-1000.00', $service->businessBalance());
        $this->assertSame('0.00', $service->unallocatedFunds());

        $service->receiveProjectPayment($project, '2000.00', '2026-10-02', null, $owner);

        $this->assertSame('1000.00', $service->projectBalance($project));
        $this->assertSame('1000.00', $service->businessBalance());
    }

    public function test_contractor_transfer_overdraws_the_project_and_company(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->projectCategory('Labour');

        $service->receiveProjectPayment($project, '500.00', '2026-10-01', null, $owner);

        $service->transferToContractor($project, $contractor, '600.00', $category->id, '2026-10-02', null, $owner);

        $this->assertSame('-100.00', $service->projectBalance($project));
        $this->assertSame('-100.00', $service->businessBalance());
        $this->assertSame('600.00', $service->contractorBalance($contractor));
    }

    public function test_owner_transfer_overdraws_unallocated_funds(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->ownerCategory();

        // All company cash is assigned to the project, leaving zero unallocated.
        $service->receiveProjectPayment($project, '1000.00', '2026-10-01', null, $owner);

        $service->transferToOwner('100.00', $category->id, '2026-10-02', null, $owner);

        $this->assertSame('900.00', $service->businessBalance());
        $this->assertSame('-100.00', $service->unallocatedFunds());
        $this->assertSame('100.00', $service->ownerBalance());
    }

    public function test_closing_an_overspent_project_moves_the_shortfall_to_unallocated_funds(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->projectCategory();

        $service->recordProjectExpense($project, '1000.00', $category->id, '2026-10-01', null, $owner);

        $this->assertSame('0.00', $service->unallocatedFunds());

        $service->requestProjectClosure($project, $owner);
        $service->finalizeProjectClosure($project, $owner);

        $this->assertSame('0.00', $service->projectBalance($project));
        $this->assertSame('-1000.00', $service->businessBalance());
        $this->assertSame('-1000.00', $service->unallocatedFunds());
    }

    public function test_receipt_is_blocked_once_project_is_closed(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');

        $service->receiveProjectPayment($project, '1000.00', '2026-10-01', null, $owner);
        $service->requestProjectClosure($project, $owner);
        $service->finalizeProjectClosure($project, $owner);

        $this->expectException(WalletException::class);

        $service->receiveProjectPayment($project, '1000.00', '2026-10-05', null, $owner);
    }

    public function test_twice_requesting_closure_is_rejected(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');

        $service->requestProjectClosure($project, $owner);

        $this->expectException(WalletException::class);

        $service->requestProjectClosure($project, $owner);
    }
}
