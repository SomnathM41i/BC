<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Services\WalletService;
use Illuminate\View\View;

class ContractorStatementController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    /**
     * Public, login-free statement the owner can share with a contractor.
     *
     * NOTE: still bound by slug (temporary location, /contractors/{slug}).
     * Phase 5 of the restructure plan moves this to an unguessable token URL.
     */
    public function show(Contractor $contractor): View
    {
        $contractor->load('type');

        return view('site.statement', [
            'contractor' => $contractor,
            'report' => $this->wallets->contractorReport($contractor),
        ]);
    }
}
