<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordManagerReturnRequest;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;

class ManagerReturnController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function store(RecordManagerReturnRequest $request, WalletTransaction $transaction): RedirectResponse
    {
        $project = $transaction->project;

        abort_if($project === null, 404);

        $this->authorize('manageFinance', $project);

        $this->wallets->recordManagerReturn(
            $transaction,
            $request->validated('amount'),
            $request->validated('transaction_date'),
            $request->validated('description'),
            $request->user(),
        );

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('status', 'Manager return recorded.');
    }
}
