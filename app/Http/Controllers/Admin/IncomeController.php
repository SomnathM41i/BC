<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReceiveUnallocatedIncomeRequest;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;

class IncomeController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function store(ReceiveUnallocatedIncomeRequest $request): RedirectResponse
    {
        $this->wallets->receiveUnallocatedIncome(
            $request->validated('amount'),
            $request->validated('transaction_date'),
            $request->validated('description'),
            $request->user(),
        );

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Unallocated company income recorded.');
    }
}
