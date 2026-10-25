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

    public function test_expense_rejected_when_project_assigned_balance_is_insufficient(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->projectCategory();

        $this->expectException(WalletException::class);

        $service->recordProjectExpense($project, '100.00', $category->id, '2026-10-01', null, $owner);
    }

    public function test_contractor_transfer_rejected_when_project_funds_are_insufficient(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->projectCategory('Labour');

        $service->receiveProjectPayment($project, '500.00', '2026-10-01', null, $owner);

        $this->expectException(WalletException::class);

        $service->transferToContractor($project, $contractor, '600.00', $category->id, '2026-10-02', null, $owner);
    }

    public function test_owner_transfer_rejected_when_unallocated_funds_are_insufficient(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->ownerCategory();

        // All company cash is assigned to the project, leaving zero unallocated.
        $service->receiveProjectPayment($project, '1000.00', '2026-10-01', null, $owner);

        $this->expectException(WalletException::class);

        $service->transferToOwner('100.00', $category->id, '2026-10-02', null, $owner);
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
