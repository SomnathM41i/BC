<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Exceptions\WalletException;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies that the Owner Personal Wallet is isolated from company cash,
 * project wallets and contractor wallets.
 */
class OwnerWalletTest extends TestCase
{
    use RefreshDatabase;

    private function service(): WalletService
    {
        return app(WalletService::class);
    }

    public function test_personal_income_only_increases_the_personal_wallet(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');
        $salary = $this->ownerIncomeCategory('Salary');

        $service->receiveProjectPayment($project, '500000.00', '2026-10-01', null, $owner);

        $businessBefore = $service->businessBalance();
        $unallocatedBefore = $service->unallocatedFunds();
        $projectBefore = $service->projectBalance($project);

        $service->recordOwnerIncome('30000.00', '2026-10-02', 'October salary', $salary->id, $owner);

        $this->assertSame('30000.00', $service->ownerBalance());
        $this->assertSame($businessBefore, $service->businessBalance());
        $this->assertSame($unallocatedBefore, $service->unallocatedFunds());
        $this->assertSame($projectBefore, $service->projectBalance($project));
    }

    public function test_personal_expense_only_reduces_the_personal_wallet(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '1000000.00');
        $expenseCategory = $this->ownerCategory('Personal Expense');

        $service->receiveProjectPayment($project, '500000.00', '2026-10-01', null, $owner);
        $service->recordOwnerIncome('30000.00', '2026-10-02', null, null, $owner);

        $businessBefore = $service->businessBalance();
        $projectBefore = $service->projectBalance($project);

        $service->recordOwnerExpense('12000.00', $expenseCategory->id, '2026-10-03', 'Groceries', $owner);

        $this->assertSame('18000.00', $service->ownerBalance());
        $this->assertSame($businessBefore, $service->businessBalance());
        $this->assertSame($projectBefore, $service->projectBalance($project));
    }

    public function test_personal_money_never_touches_projects_or_contractor_wallets(): void
    {
        $service = $this->service();
        $owner = $this->owner();
        $contractor = $this->contractor('Ramesh');
        $project = $this->makeProject($contractor, '1000000.00');
        $labour = $this->projectCategory('Labour');

        $service->receiveProjectPayment($project, '500000.00', '2026-10-01', null, $owner);
        $service->transferToContractor($project, $contractor, '50000.00', $labour->id, '2026-10-02', null, $owner);

        $businessBefore = $service->businessBalance();
        $projectBefore = $service->projectBalance($project);
        $contractorBefore = $service->contractorBalance($contractor);
        $costsBefore = $service->totalProjectCosts($project);

        $service->recordOwnerIncome('25000.00', '2026-10-03', 'Rent received', null, $owner);
        $service->recordOwnerExpense('5000.00', null, '2026-10-04', 'Personal', $owner);

        $this->assertSame('20000.00', $service->ownerBalance());
        $this->assertSame($businessBefore, $service->businessBalance());
        $this->assertSame($projectBefore, $service->projectBalance($project));
        $this->assertSame($contractorBefore, $service->contractorBalance($contractor));
        $this->assertSame($costsBefore, $service->totalProjectCosts($project));
    }

    public function test_transfer_to_company_moves_personal_money_without_duplicate_income(): void
    {
        $service = $this->service();
        $owner = $this->owner();

        // Personal money that never entered company cash.
        $service->recordOwnerIncome('50000.00', '2026-10-01', 'Personal savings', null, $owner);

        $this->assertSame('0.00', $service->businessBalance());
        $this->assertSame('0.00', $service->unallocatedFunds());
        $this->assertSame('50000.00', $service->ownerBalance());

        $service->returnOwnerFunds('20000.00', '2026-10-02', 'Funding the company', $owner);

        // Company cash increased exactly once, by the transfer amount.
        $this->assertSame('20000.00', $service->businessBalance());
        $this->assertSame('20000.00', $service->unallocatedFunds());
        $this->assertSame('30000.00', $service->ownerBalance());

        // The movement is a single transfer entry, not a new external receipt.
        $this->assertSame(1, WalletTransaction::query()->ofType(TransactionType::OwnerReturn)->count());
        $this->assertSame(0, WalletTransaction::query()->ofType(TransactionType::Receipt)->count());
        $this->assertSame('20000.00', $service->ownerTransferredToCompanyTotal());
        $this->assertSame('50000.00', $service->ownerIncomeTotal());
    }

    public function test_transfer_to_company_is_blocked_when_personal_wallet_is_insufficient(): void
    {
        $service = $this->service();
        $owner = $this->owner();

        $service->recordOwnerIncome('100.00', '2026-10-01', null, null, $owner);

        $this->expectException(WalletException::class);

        $service->returnOwnerFunds('500.00', '2026-10-02', null, $owner);
    }

    public function test_personal_income_rejects_a_project_category(): void
    {
        $owner = $this->owner();
        $projectCategory = $this->projectCategory('Materials');

        $this->actingAs($owner)
            ->post('/owner/income', [
                'amount' => '1000',
                'category_id' => $projectCategory->id,
                'transaction_date' => '2026-10-01',
            ])
            ->assertSessionHasErrors('category_id');
    }

    public function test_owner_can_record_personal_income_through_the_owner_page(): void
    {
        $owner = $this->owner();
        $salary = $this->ownerIncomeCategory('Salary');

        $this->actingAs($owner)
            ->post('/owner/income', [
                'amount' => '25000',
                'category_id' => $salary->id,
                'transaction_date' => '2026-10-01',
                'description' => 'Salary',
            ])
            ->assertRedirect(route('owner.show'));

        $wallets = app(WalletService::class);
        $this->assertSame('25000.00', $wallets->ownerBalance());
        $this->assertSame('0.00', $wallets->businessBalance());

        $this->actingAs($owner)
            ->get('/owner')
            ->assertOk()
            ->assertSee('Personal Wallet Balance')
            ->assertSee('Personal Income')
            ->assertSee('Record Personal Income')
            ->assertSee('Transfer to Company');
    }
}
