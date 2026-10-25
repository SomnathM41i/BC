<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FundProjectRequest;
use App\Http\Requests\Admin\ReceiveProjectPaymentRequest;
use App\Http\Requests\Admin\RecordProjectExpenseRequest;
use App\Http\Requests\Admin\TransferToContractorRequest;
use App\Models\Contractor;
use App\Models\Project;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;

class ProjectFinanceController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function storeReceipt(ReceiveProjectPaymentRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('manageFinance', $project);

        $this->wallets->receiveProjectPayment(
            $project,
            $request->validated('amount'),
            $request->validated('transaction_date'),
            $request->validated('description'),
            $request->user(),
        );

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('status', 'Project payment recorded.');
    }

    public function storeExpense(RecordProjectExpenseRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('manageFinance', $project);

        $this->wallets->recordProjectExpense(
            $project,
            $request->validated('amount'),
            (int) $request->validated('category_id'),
            $request->validated('transaction_date'),
            $request->validated('description'),
            $request->user(),
        );

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('status', 'Direct project expense recorded.');
    }

    public function storeFund(FundProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('manageFinance', $project);

        $this->wallets->fundProject(
            $project,
            $request->validated('amount'),
            $request->validated('transaction_date'),
            $request->validated('description'),
            $request->user(),
        );

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('status', 'Unallocated company funds assigned to the project.');
    }

    public function storeTransfer(TransferToContractorRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('manageFinance', $project);

        $contractor = $this->resolveContractor($request);

        $this->wallets->transferToContractor(
            $project,
            $contractor,
            $request->validated('amount'),
            (int) $request->validated('category_id'),
            $request->validated('transaction_date'),
            $request->validated('description'),
            $request->user(),
        );

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('status', 'Contractor cost transfer recorded.');
    }

    /**
     * Resolve the contractor for a transfer: either an existing one picked from
     * the dropdown, or one created on the spot from the submitted name and phone.
     */
    private function resolveContractor(TransferToContractorRequest $request): Contractor
    {
        if ($request->validated('contractor_id') !== TransferToContractorRequest::NEW_CONTRACTOR) {
            return Contractor::query()->findOrFail($request->validated('contractor_id'));
        }

        $contractor = Contractor::create([
            'name' => $request->validated('new_contractor_name'),
            'phone' => $request->validated('new_contractor_phone'),
            'contractor_type_id' => null,
            'is_active' => true,
        ]);

        // Auto-create the contractor wallet so the advance can be tracked immediately.
        $this->wallets->contractorWallet($contractor);

        return $contractor;
    }
}
