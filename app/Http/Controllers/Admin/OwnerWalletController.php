<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\TransactionType;
use App\Http\Requests\Admin\RecordOwnerExpenseRequest;
use App\Http\Requests\Admin\RecordOwnerIncomeRequest;
use App\Http\Requests\Admin\ReturnOwnerFundsRequest;
use App\Http\Requests\Admin\TransferToOwnerRequest;
use App\Models\ExpenseCategory;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerWalletController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function show(Request $request): View
    {
        abort_unless($request->user()->isOwner(), 403);

        $transactions = WalletTransaction::query()
            ->with(['category', 'creator'])
            ->whereIn('type', [
                TransactionType::OwnerIncome->value,
                TransactionType::OwnerTransfer->value,
                TransactionType::OwnerExpense->value,
                TransactionType::OwnerReturn->value,
            ])
            ->recentFirst()
            ->get();

        $summary = $this->wallets->ownerSummary();

        // Walk back from the current balance so the newest row always matches
        // the wallet, showing what each entry left in it.
        $balance = $summary['balance'];

        foreach ($transactions as $transaction) {
            $transaction->balance_after = $balance;
            $balance = in_array($transaction->type, [TransactionType::OwnerExpense, TransactionType::OwnerReturn], true)
                ? Money::add($balance, $transaction->amount)
                : Money::sub($balance, $transaction->amount);
        }

        return view('admin.owner.show', [
            'summary' => $summary,
            'businessBalance' => $this->wallets->businessBalance(),
            'unallocated' => $this->wallets->unallocatedFunds(),
            'transactions' => $transactions,
            'categories' => ExpenseCategory::query()->forOwner()->active()->orderBy('name')->get(),
            'incomeCategories' => ExpenseCategory::query()->forOwnerIncome()->active()->orderBy('name')->get(),
        ]);
    }

    public function income(RecordOwnerIncomeRequest $request): RedirectResponse
    {
        $this->wallets->recordOwnerIncome(
            $request->validated('amount'),
            $request->validated('transaction_date'),
            $request->validated('description'),
            $request->validated('category_id') !== null ? (int) $request->validated('category_id') : null,
            $request->user(),
        );

        return redirect()
            ->route('admin.owner.show')
            ->with('status', 'Personal income recorded in the owner personal wallet.');
    }

    public function transfer(TransferToOwnerRequest $request): RedirectResponse
    {
        $this->wallets->transferToOwner(
            $request->validated('amount'),
            (int) $request->validated('category_id'),
            $request->validated('transaction_date'),
            $request->validated('description'),
            $request->user(),
        );

        return redirect()
            ->route('admin.owner.show')
            ->with('status', 'Transfer to Owner Personal Wallet recorded.');
    }

    public function returnFunds(ReturnOwnerFundsRequest $request): RedirectResponse
    {
        $this->wallets->returnOwnerFunds(
            $request->validated('amount'),
            $request->validated('transaction_date'),
            $request->validated('description'),
            $request->user(),
        );

        return redirect()
            ->route('admin.owner.show')
            ->with('status', 'Transfer to company wallet recorded.');
    }

    public function expense(RecordOwnerExpenseRequest $request): RedirectResponse
    {
        $this->wallets->recordOwnerExpense(
            $request->validated('amount'),
            $request->validated('category_id') !== null ? (int) $request->validated('category_id') : null,
            $request->validated('transaction_date'),
            $request->validated('description'),
            $request->user(),
        );

        $balance = $this->wallets->ownerBalance();

        return redirect()
            ->route('admin.owner.show')
            ->with('status', Money::lt($balance, '0')
                ? 'Personal expense recorded. Personal wallet is now '.Money::inr($balance).'.'
                : 'Personal expense recorded.');
    }
}
