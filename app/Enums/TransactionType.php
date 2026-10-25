<?php

namespace App\Enums;

enum TransactionType: string
{
    case Receipt = 'receipt';
    case ProjectExpense = 'project_expense';
    case ContractorTransfer = 'contractor_transfer';
    case OwnerTransfer = 'owner_transfer';
    case OwnerExpense = 'owner_expense';
    case OwnerIncome = 'owner_income';
    case OwnerReturn = 'owner_return';
    case ProjectAllocation = 'project_allocation';
    case ClosureRelease = 'closure_release';

    public function label(): string
    {
        return match ($this) {
            self::Receipt => 'Payment Received',
            self::ProjectExpense => 'Project Expense (Direct)',
            self::ContractorTransfer => 'Contractor Payment',
            self::OwnerTransfer => 'Transfer to Owner',
            self::OwnerExpense => 'Personal Expense',
            self::OwnerIncome => 'Personal Income',
            self::OwnerReturn => 'Transfer to Company',
            self::ProjectAllocation => 'Project Funding',
            self::ClosureRelease => 'Funds Released (Closure)',
        };
    }

    /**
     * Effect on the BUSINESS wallet / total company cash.
     *
     * Personal income and personal expenses never touch company cash: the owner
     * wallet holds money that already left company cash (via a transfer to the
     * owner), so those two types have no company cash effect. Project funding
     * and closure release only reclassify money that is already company cash.
     *
     *  +1 = increases company cash, -1 = decreases company cash, 0 = no effect.
     */
    public function businessCashEffect(): int
    {
        return match ($this) {
            self::Receipt, self::OwnerReturn => 1,
            self::ProjectExpense, self::ContractorTransfer, self::OwnerTransfer => -1,
            self::OwnerIncome, self::OwnerExpense, self::ProjectAllocation, self::ClosureRelease => 0,
        };
    }

    /**
     * Direction of the wallet this entry moves, used only for ledger
     * colour-coding: positive is shown as money in (green), negative as money
     * out (red), zero as a neutral reclassification (grey).
     */
    public function ledgerDirection(): int
    {
        return match ($this) {
            self::Receipt, self::OwnerIncome, self::OwnerReturn => 1,
            self::ProjectExpense, self::ContractorTransfer, self::OwnerTransfer, self::OwnerExpense => -1,
            self::ProjectAllocation, self::ClosureRelease => 0,
        };
    }

    /**
     * Whether this transaction reads as money in (green) in the ledger.
     */
    public function isIncome(): bool
    {
        return $this->ledgerDirection() > 0;
    }

    /**
     * Whether this transaction reads as money out (red) in the ledger.
     */
    public function isExpense(): bool
    {
        return $this->ledgerDirection() < 0;
    }

    /**
     * Tailwind text colour for a ledger entry: green for money in, red for
     * money out, grey for a neutral reclassification.
     */
    public function textColorClass(): string
    {
        return match (true) {
            $this->isIncome() => 'text-emerald-600',
            $this->isExpense() => 'text-rose-600',
            default => 'text-slate-500',
        };
    }

    /**
     * Whether this transaction counts as a project cost.
     */
    public function isProjectCost(): bool
    {
        return in_array($this, [self::ProjectExpense, self::ContractorTransfer], true);
    }

    /**
     * Whether this transaction belongs to the owner's personal wallet rather
     * than the business/project ledger.
     */
    public function isPersonal(): bool
    {
        return in_array($this, [self::OwnerIncome, self::OwnerExpense], true);
    }
}
