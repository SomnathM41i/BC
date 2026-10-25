<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\TransactionType;
use App\Enums\WalletType;
use App\Exceptions\WalletException;
use App\Models\Contractor;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The single source of truth for every money movement and balance change.
 *
 * Wallet model
 * ------------
 * - Business Wallet balance is the company's overall actual cash.
 * - Each Project Wallet balance is the portion of company cash currently
 *   assigned to that project (a tracking sub-balance, not extra cash).
 * - The Owner Personal Wallet is a separate pot funded from unallocated
 *   company funds and is excluded from company cash. Money can also be moved
 *   back from the Owner Personal Wallet into company cash.
 * - Each Contractor Wallet tracks the cumulative advance given to that
 *   contractor. Money in a contractor wallet has already left company cash;
 *   the wallet is an additive tracking layer, not additional money.
 * - Unallocated company funds can be assigned to a project (fundProject) or
 *   released from a project (closure); both are reclassifications that leave
 *   total company cash unchanged.
 *
 * Invariant: Unallocated funds = Business balance - sum(active project assigned balances).
 * Contractor wallet balances do NOT participate in this invariant.
 */
class WalletService
{
    /*
    |--------------------------------------------------------------------------
    | Wallet accessors
    |--------------------------------------------------------------------------
    */

    public function businessWallet(): Wallet
    {
        return Wallet::query()->firstOrCreate(
            ['type' => WalletType::Business->value, 'project_id' => null, 'contractor_id' => null],
            ['balance' => '0.00']
        );
    }

    public function ownerWallet(): Wallet
    {
        return Wallet::query()->firstOrCreate(
            ['type' => WalletType::Owner->value, 'project_id' => null, 'contractor_id' => null],
            ['balance' => '0.00']
        );
    }

    public function projectWallet(Project $project): Wallet
    {
        return Wallet::query()->firstOrCreate(
            ['type' => WalletType::Project->value, 'project_id' => $project->id],
            ['balance' => '0.00']
        );
    }

    public function contractorWallet(Contractor $contractor): Wallet
    {
        return Wallet::query()->firstOrCreate(
            ['type' => WalletType::Contractor->value, 'contractor_id' => $contractor->id],
            ['balance' => '0.00']
        );
    }

    public function businessBalance(): string
    {
        return Money::normalize((string) $this->businessWallet()->balance);
    }

    public function ownerBalance(): string
    {
        return Money::normalize((string) $this->ownerWallet()->balance);
    }

    public function projectBalance(Project $project): string
    {
        return Money::normalize((string) $this->projectWallet($project)->balance);
    }

    public function contractorBalance(Contractor $contractor): string
    {
        return Money::normalize((string) $this->contractorWallet($contractor)->balance);
    }

    /**
     * Sum of balances currently assigned to project wallets.
     */
    public function assignedProjectTotal(): string
    {
        $balances = Wallet::query()
            ->where('type', WalletType::Project->value)
            ->pluck('balance');

        return Money::sum($balances);
    }

    /**
     * Unallocated company funds available for new projects or owner transfers.
     */
    public function unallocatedFunds(): string
    {
        return Money::sub($this->businessBalance(), $this->assignedProjectTotal());
    }

    /*
    |--------------------------------------------------------------------------
    | Money-in operations
    |--------------------------------------------------------------------------
    */

    /**
     * Money received specifically for a project.
     * Increases company cash and the project's assigned balance together.
     */
    public function receiveProjectPayment(
        Project $project,
        string $amount,
        string $date,
        ?string $description,
        ?User $actor = null
    ): WalletTransaction {
        $amount = $this->assertPositive($amount);

        return DB::transaction(function () use ($project, $amount, $date, $description, $actor) {
            $project = $this->lockProject($project);

            if (! $project->isActive()) {
                throw new WalletException('Payments can only be recorded for an active project.');
            }

            $business = $this->lockBusinessWallet();
            $wallet = $this->lockProjectWallet($project);

            $this->credit($business, $amount, 'company cash');
            $this->credit($wallet, $amount, 'project assigned balance');

            return $this->createTransaction([
                'type' => TransactionType::Receipt,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description,
                'project_id' => $project->id,
                'destination_wallet_id' => $wallet->id,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /**
     * Money received that is not linked to a project. Increases company cash only.
     */
    public function receiveUnallocatedIncome(
        string $amount,
        string $date,
        ?string $description,
        ?User $actor = null
    ): WalletTransaction {
        $amount = $this->assertPositive($amount);

        return DB::transaction(function () use ($amount, $date, $description, $actor) {
            $business = $this->lockBusinessWallet();
            $this->credit($business, $amount, 'company cash');

            return $this->createTransaction([
                'type' => TransactionType::Receipt,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description,
                'destination_wallet_id' => $business->id,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /**
     * Assign unallocated company funds to a project's assigned balance.
     *
     * This is a reclassification, not a new cash receipt: the money is already
     * part of company cash in the Business Wallet, so total company cash does
     * not change. Only unallocated funds (company cash not already assigned to
     * a project) can be assigned.
     */
    public function fundProject(
        Project $project,
        string $amount,
        string $date,
        ?string $description,
        ?User $actor = null
    ): WalletTransaction {
        $amount = $this->assertPositive($amount);

        return DB::transaction(function () use ($project, $amount, $date, $description, $actor) {
            $project = $this->lockProject($project);

            if (! $project->isActive()) {
                throw new WalletException('Funds can only be assigned to an active project.');
            }

            $business = $this->lockBusinessWallet();
            $wallet = $this->lockProjectWallet($project);

            $unallocated = Money::sub((string) $business->balance, $this->assignedProjectTotal());

            if (Money::lt($unallocated, $amount)) {
                throw new WalletException(
                    'Insufficient unallocated funds. Available: '.Money::inr($unallocated).'.'
                );
            }

            // Reclassification only: company cash already includes this money.
            $this->credit($wallet, $amount, 'project assigned balance');

            return $this->createTransaction([
                'type' => TransactionType::ProjectAllocation,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description,
                'project_id' => $project->id,
                'source_wallet_id' => $business->id,
                'destination_wallet_id' => $wallet->id,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Money-out operations
    |--------------------------------------------------------------------------
    */

    /**
     * Direct project expense. Decreases company cash and the project's assigned
     * balance together, and is recorded once as a project cost.
     */
    public function recordProjectExpense(
        Project $project,
        string $amount,
        int $categoryId,
        string $date,
        ?string $description,
        ?User $actor = null
    ): WalletTransaction {
        $amount = $this->assertPositive($amount);

        return DB::transaction(function () use ($project, $amount, $categoryId, $date, $description, $actor) {
            $project = $this->lockProject($project);

            if (! $project->isActive()) {
                throw new WalletException('Expenses can only be recorded for an active project.');
            }

            $this->assertProjectCategory($categoryId);

            $business = $this->lockBusinessWallet();
            $wallet = $this->lockProjectWallet($project);

            $this->debit($wallet, $amount, 'project assigned balance');
            $this->debit($business, $amount, 'company cash');

            return $this->createTransaction([
                'type' => TransactionType::ProjectExpense,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description,
                'project_id' => $project->id,
                'category_id' => $categoryId,
                'source_wallet_id' => $wallet->id,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /**
     * Transfer money to a contractor for a project. Recorded as a project cost.
     * The contractor wallet tracks the cumulative advance given.
     */
    public function transferToContractor(
        Project $project,
        Contractor $contractor,
        string $amount,
        int $categoryId,
        string $date,
        ?string $description,
        ?User $actor = null
    ): WalletTransaction {
        $amount = $this->assertPositive($amount);

        return DB::transaction(function () use ($project, $contractor, $amount, $categoryId, $date, $description, $actor) {
            $project = $this->lockProject($project);

            if (! $project->isActive()) {
                throw new WalletException('Contractor transfers can only be recorded for an active project.');
            }

            if (! $contractor->is_active) {
                throw new WalletException('The selected contractor is not active.');
            }

            $this->assertProjectCategory($categoryId);

            $business = $this->lockBusinessWallet();
            $wallet = $this->lockProjectWallet($project);
            $contractorWallet = $this->lockContractorWallet($contractor);

            $this->debit($wallet, $amount, 'project assigned balance');
            $this->debit($business, $amount, 'company cash');
            $this->credit($contractorWallet, $amount, 'contractor advance');

            return $this->createTransaction([
                'type' => TransactionType::ContractorTransfer,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description,
                'project_id' => $project->id,
                'category_id' => $categoryId,
                'contractor_id' => $contractor->id,
                'source_wallet_id' => $wallet->id,
                'destination_wallet_id' => $contractorWallet->id,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /**
     * Transfer unallocated company funds to the Owner Personal Wallet.
     */
    public function transferToOwner(
        string $amount,
        int $categoryId,
        string $date,
        ?string $description,
        ?User $actor = null
    ): WalletTransaction {
        $amount = $this->assertPositive($amount);

        return DB::transaction(function () use ($amount, $categoryId, $date, $description, $actor) {
            $this->assertOwnerCategory($categoryId);

            $business = $this->lockBusinessWallet();
            $owner = $this->lockOwnerWallet();

            $unallocated = Money::sub((string) $business->balance, $this->assignedProjectTotal());

            if (Money::lt($unallocated, $amount)) {
                throw new WalletException(
                    'Insufficient unallocated funds. Available: '.Money::inr($unallocated).'.'
                );
            }

            $this->debit($business, $amount, 'company cash');
            $this->credit($owner, $amount, 'owner personal wallet');

            return $this->createTransaction([
                'type' => TransactionType::OwnerTransfer,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description,
                'category_id' => $categoryId,
                'source_wallet_id' => $business->id,
                'destination_wallet_id' => $owner->id,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /**
     * Move money from the Owner Personal Wallet back into company funds.
     * Increases company cash and reduces the owner's personal balance.
     */
    public function returnOwnerFunds(
        string $amount,
        string $date,
        ?string $description,
        ?User $actor = null
    ): WalletTransaction {
        $amount = $this->assertPositive($amount);

        return DB::transaction(function () use ($amount, $date, $description, $actor) {
            $business = $this->lockBusinessWallet();
            $owner = $this->lockOwnerWallet();

            $this->debit($owner, $amount, 'owner personal wallet');
            $this->credit($business, $amount, 'company cash');

            return $this->createTransaction([
                'type' => TransactionType::OwnerReturn,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description,
                'source_wallet_id' => $owner->id,
                'destination_wallet_id' => $business->id,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /**
     * Personal income received by the owner from a personal source (salary,
     * rent, interest, ...). Increases the Owner Personal Wallet ONLY.
     *
     * This is deliberately not company income: it never touches company cash,
     * unallocated funds, any project wallet or any contractor wallet. The money
     * reaches the business only later, through an explicit Transfer to Company.
     */
    public function recordOwnerIncome(
        string $amount,
        string $date,
        ?string $description,
        ?int $categoryId = null,
        ?User $actor = null
    ): WalletTransaction {
        $amount = $this->assertPositive($amount);

        return DB::transaction(function () use ($amount, $date, $description, $categoryId, $actor) {
            if ($categoryId !== null) {
                $this->assertOwnerIncomeCategory($categoryId);
            }

            $owner = $this->lockOwnerWallet();
            $this->credit($owner, $amount, 'owner personal wallet');

            return $this->createTransaction([
                'type' => TransactionType::OwnerIncome,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description,
                'category_id' => $categoryId,
                'destination_wallet_id' => $owner->id,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /**
     * Personal expense paid from the Owner Personal Wallet. Does not affect project costs.
     */
    public function recordOwnerExpense(
        string $amount,
        ?int $categoryId,
        string $date,
        ?string $description,
        ?User $actor = null
    ): WalletTransaction {
        $amount = $this->assertPositive($amount);

        return DB::transaction(function () use ($amount, $categoryId, $date, $description, $actor) {
            if ($categoryId !== null) {
                $this->assertOwnerCategory($categoryId);
            }

            $owner = $this->lockOwnerWallet();
            $this->debit($owner, $amount, 'owner personal wallet');

            return $this->createTransaction([
                'type' => TransactionType::OwnerExpense,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description,
                'category_id' => $categoryId,
                'source_wallet_id' => $owner->id,
                'created_by' => $actor?->id,
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Project closure
    |--------------------------------------------------------------------------
    */

    /**
     * Step 1 of closure: mark the project as Closure Pending and block new
     * expenses and contractor transfers.
     */
    public function requestProjectClosure(Project $project, ?User $actor = null): Project
    {
        return DB::transaction(function () use ($project) {
            $project = $this->lockProject($project);

            if ($project->isClosed()) {
                throw new WalletException('This project is already closed.');
            }

            if ($project->isClosurePending()) {
                throw new WalletException('Closure is already pending for this project.');
            }

            $project->status = ProjectStatus::ClosurePending;
            $project->save();

            return $project;
        });
    }

    /**
     * Step 2 of closure: release the remaining assigned balance back into
     * unallocated company funds and mark the project Closed.
     *
     * This is a reclassification, not a new cash receipt. Total company cash
     * does not change.
     */
    public function finalizeProjectClosure(Project $project, ?User $actor = null): Project
    {
        return DB::transaction(function () use ($project, $actor) {
            $project = $this->lockProject($project);

            if ($project->isClosed()) {
                throw new WalletException('This project is already closed.');
            }

            if (! $project->isClosurePending()) {
                throw new WalletException('Request closure before finalizing it.');
            }

            $business = $this->lockBusinessWallet();
            $wallet = $this->lockProjectWallet($project);

            $remaining = Money::normalize((string) $wallet->balance);

            if (Money::gt($remaining, '0')) {
                // Reclassification only: the money is already part of company
                // cash in the Business Wallet. Releasing it from the project
                // assignment must NOT increase total company cash.
                $this->debit($wallet, $remaining, 'project assigned balance');

                $this->createTransaction([
                    'type' => TransactionType::ClosureRelease,
                    'amount' => $remaining,
                    'transaction_date' => now()->toDateString(),
                    'description' => 'Remaining assigned balance released to unallocated company funds.',
                    'project_id' => $project->id,
                    'source_wallet_id' => $wallet->id,
                    'destination_wallet_id' => $business->id,
                    'created_by' => $actor?->id,
                ]);
            }

            $project->status = ProjectStatus::Closed;
            $project->closed_at = now();
            $project->save();

            return $project;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reporting helpers
    |--------------------------------------------------------------------------
    */

    public function directProjectCosts(Project $project): string
    {
        return $this->sumTransactions(
            WalletTransaction::query()->where('project_id', $project->id)
                ->ofType(TransactionType::ProjectExpense)
        );
    }

    public function contractorTransfersTotal(Project $project): string
    {
        return $this->sumTransactions(
            WalletTransaction::query()->where('project_id', $project->id)
                ->ofType(TransactionType::ContractorTransfer)
        );
    }

    /**
     * Total project costs = direct costs + contractor transfers.
     */
    public function totalProjectCosts(Project $project): string
    {
        return Money::add(
            $this->directProjectCosts($project),
            $this->contractorTransfersTotal($project)
        );
    }

    /**
     * Full wallet/cost snapshot for a project, used by views and the dashboard.
     *
     * @return array<string, string>
     */
    public function projectReport(Project $project): array
    {
        $direct = $this->directProjectCosts($project);
        $transfers = $this->contractorTransfersTotal($project);

        return [
            'agreed_amount' => Money::normalize((string) $project->agreed_amount),
            'total_received' => $project->totalReceived(),
            'remaining_against_agreed' => $project->remainingAgainstAgreed(),
            'assigned_balance' => $this->projectBalance($project),
            'direct_costs' => $direct,
            'contractor_transfers' => $transfers,
            'total_costs' => Money::add($direct, $transfers),
        ];
    }

    /**
     * Total amount paid to each contractor, keyed by contractor id.
     *
     * @return array<int, string>
     */
    public function contractorTotals(): array
    {
        return WalletTransaction::query()
            ->whereNotNull('contractor_id')
            ->ofType(TransactionType::ContractorTransfer)
            ->groupBy('contractor_id')
            ->selectRaw('contractor_id, SUM(amount) as total')
            ->pluck('total', 'contractor_id')
            ->map(fn ($total) => Money::normalize((string) $total))
            ->all();
    }

    /**
     * How much a contractor was paid, broken down per project, plus the grand total.
     * Includes projects the contractor is assigned to even before any payment.
     *
     * @return array{rows: list<array<string, mixed>>, total: string}
     */
    public function contractorReport(Contractor $contractor): array
    {
        $aggregates = WalletTransaction::query()
            ->where('contractor_id', $contractor->id)
            ->ofType(TransactionType::ContractorTransfer)
            ->whereNotNull('project_id')
            ->groupBy('project_id')
            ->selectRaw('project_id, SUM(amount) as total, COUNT(*) as payments, MAX(transaction_date) as last_date')
            ->get()
            ->keyBy('project_id');

        $projectIds = $aggregates->keys()
            ->merge($contractor->projects()->pluck('id'))
            ->unique()
            ->all();

        $projects = Project::query()
            ->whereIn('id', $projectIds)
            ->orderBy('name')
            ->get();

        $rows = $projects->map(function (Project $project) use ($aggregates): array {
            $aggregate = $aggregates->get($project->id);

            return [
                'project' => $project,
                'payments' => (int) ($aggregate->payments ?? 0),
                'total' => Money::normalize((string) ($aggregate->total ?? '0.00')),
                'last_date' => $aggregate->last_date ?? null,
            ];
        })->all();

        return [
            'rows' => $rows,
            'total' => $this->sumTransactions(
                WalletTransaction::query()
                    ->where('contractor_id', $contractor->id)
                    ->ofType(TransactionType::ContractorTransfer)
            ),
        ];
    }

    /**
     * Owner Personal Wallet snapshot for the owner page.
     *
     * @return array<string, string>
     */
    public function ownerSummary(): array
    {
        return [
            'balance' => $this->ownerBalance(),
            'income' => $this->ownerIncomeTotal(),
            'expenses' => $this->ownerExpenseTotal(),
            'from_company' => $this->ownerFundedFromCompanyTotal(),
            'to_company' => $this->ownerTransferredToCompanyTotal(),
        ];
    }

    /**
     * Total personal income recorded in the Owner Personal Wallet.
     */
    public function ownerIncomeTotal(): string
    {
        return $this->sumTransactions(
            WalletTransaction::query()->ofType(TransactionType::OwnerIncome)
        );
    }

    /**
     * Total personal expenses recorded in the Owner Personal Wallet.
     */
    public function ownerExpenseTotal(): string
    {
        return $this->sumTransactions(
            WalletTransaction::query()->ofType(TransactionType::OwnerExpense)
        );
    }

    /**
     * Total company cash explicitly moved into the Owner Personal Wallet.
     */
    public function ownerFundedFromCompanyTotal(): string
    {
        return $this->sumTransactions(
            WalletTransaction::query()->ofType(TransactionType::OwnerTransfer)
        );
    }

    /**
     * Total personal money explicitly transferred back into company cash.
     */
    public function ownerTransferredToCompanyTotal(): string
    {
        return $this->sumTransactions(
            WalletTransaction::query()->ofType(TransactionType::OwnerReturn)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Internal helpers
    |--------------------------------------------------------------------------
    */

    protected function assertPositive(string|int|float $amount): string
    {
        $amount = Money::normalize($amount);

        if (Money::lte($amount, '0')) {
            throw new WalletException('Amount must be greater than zero.');
        }

        return $amount;
    }

    protected function lockProject(Project $project): Project
    {
        return Project::query()->whereKey($project->id)->lockForUpdate()->firstOrFail();
    }

    protected function lockBusinessWallet(): Wallet
    {
        $this->businessWallet();

        return Wallet::query()
            ->where('type', WalletType::Business->value)
            ->whereNull('project_id')
            ->whereNull('contractor_id')
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function lockOwnerWallet(): Wallet
    {
        $this->ownerWallet();

        return Wallet::query()
            ->where('type', WalletType::Owner->value)
            ->whereNull('project_id')
            ->whereNull('contractor_id')
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function lockProjectWallet(Project $project): Wallet
    {
        $this->projectWallet($project);

        return Wallet::query()
            ->where('type', WalletType::Project->value)
            ->where('project_id', $project->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function lockContractorWallet(Contractor $contractor): Wallet
    {
        $this->contractorWallet($contractor);

        return Wallet::query()
            ->where('type', WalletType::Contractor->value)
            ->where('contractor_id', $contractor->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function credit(Wallet $wallet, string $amount, string $label): void
    {
        $wallet->balance = Money::add((string) $wallet->balance, $amount);
        $wallet->save();
    }

    protected function debit(Wallet $wallet, string $amount, string $label): void
    {
        if (Money::lt((string) $wallet->balance, $amount)) {
            throw new WalletException('Insufficient '.$label.'. Available: '.Money::inr($wallet->balance).'.');
        }

        $wallet->balance = Money::sub((string) $wallet->balance, $amount);
        $wallet->save();
    }

    protected function assertProjectCategory(int $categoryId): void
    {
        $exists = ExpenseCategory::query()
            ->whereKey($categoryId)
            ->where('context', ExpenseCategory::CONTEXT_PROJECT)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            throw new WalletException('The selected expense category is not valid.');
        }
    }

    protected function assertOwnerCategory(int $categoryId): void
    {
        $exists = ExpenseCategory::query()
            ->whereKey($categoryId)
            ->where('context', ExpenseCategory::CONTEXT_OWNER)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            throw new WalletException('The selected classification is not valid.');
        }
    }

    protected function assertOwnerIncomeCategory(int $categoryId): void
    {
        $exists = ExpenseCategory::query()
            ->whereKey($categoryId)
            ->where('context', ExpenseCategory::CONTEXT_OWNER_INCOME)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            throw new WalletException('The selected personal income category is not valid.');
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createTransaction(array $attributes): WalletTransaction
    {
        return WalletTransaction::create($attributes);
    }

    protected function sumTransactions(Builder $query): string
    {
        return Money::sum($query->pluck('amount'));
    }
}
