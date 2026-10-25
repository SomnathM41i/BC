<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\RecordOwnerExpenseRequest;
use App\Http\Requests\RecordOwnerIncomeRequest;
use App\Http\Requests\ReturnOwnerFundsRequest;
use App\Http\Requests\TransferToOwnerRequest;
use App\Models\ExpenseCategory;
use App\Models\WalletTransaction;
use App\Services\WalletService;
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

        return view('owner.show', [
            'summary' => $this->wallets->ownerSummary(),
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
            ->route('owner.show')
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
            ->route('owner.show')
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
            ->route('owner.show')
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

        return redirect()
            ->route('owner.show')
            ->with('status', 'Personal expense recorded.');
    }
}
