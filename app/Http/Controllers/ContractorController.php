<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractorRequest;
use App\Models\Contractor;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContractorController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function index(): View
    {
        $contractors = Contractor::query()
            ->with('type')
            ->withCount('projects')
            ->orderBy('name')
            ->get();

        return view('contractors.index', [
            'contractors' => $contractors,
            'totals' => $this->wallets->contractorTotals(),
        ]);
    }

    public function show(Contractor $contractor): View
    {
        $contractor->load('type');

        return view('contractors.show', [
            'contractor' => $contractor,
            'report' => $this->wallets->contractorReport($contractor),
        ]);
    }

    public function store(StoreContractorRequest $request): RedirectResponse
    {
        $contractor = Contractor::create([
            'name' => $request->validated('name'),
            'phone' => $request->validated('phone'),
            'contractor_type_id' => $request->validated('contractor_type_id'),
            'notes' => $request->validated('notes'),
            'is_active' => true,
        ]);

        // Auto-create the contractor wallet so advances can be tracked immediately.
        $this->wallets->contractorWallet($contractor);

        return redirect()
            ->route('admin.contractors.index')
            ->with('status', 'Contractor created successfully.');
    }
}
