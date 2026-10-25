@extends('layouts.app')

@section('title', $project->name)

@php
    $statusClasses = [
        'active' => 'bg-emerald-100 text-emerald-800',
        'closure_pending' => 'bg-amber-100 text-amber-800',
        'closed' => 'bg-slate-200 text-slate-700',
    ];
    $today = now()->toDateString();
@endphp

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 underline">
                <x-icon name="back" class="h-3.5 w-3.5" />
                Projects
            </a>
            <h1 class="mt-1 text-2xl font-semibold text-slate-900">{{ $project->name }}</h1>
            <p class="text-sm text-slate-500">
                {{ $project->location ?: 'No location' }} · Contractor: {{ $project->contractor?->name ?? 'Unassigned' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusClasses[$project->status->value] }}">
                {{ $project->status->label() }}
            </span>
            @if ($isOwner && $project->isActive())
                <form method="POST" action="{{ route('projects.request-closure', $project) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md border border-amber-300 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-800 hover:bg-amber-100">
                        <x-icon name="lock" />
                        Request Closure
                    </button>
                </form>
            @endif
            @if ($isOwner && $project->isClosurePending())
                <form method="POST" action="{{ route('projects.finalize-closure', $project) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
                        <x-icon name="check-circle" />
                        Close Project
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Contract Value</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">@inr($report['agreed_amount'])</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Received</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">@inr($report['total_received'])</p>
            <p class="text-xs text-slate-500">Outstanding @inr($report['remaining_against_agreed'])</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Available Balance</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">@inr($report['assigned_balance'])</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Spend</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">@inr($report['total_costs'])</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Direct Costs</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">@inr($report['direct_costs'])</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Contractor Payments</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">@inr($report['contractor_transfers'])</p>
        </div>
    </div>

    @if ($project->isClosed())
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-1 font-semibold text-slate-900">Project Closed</h2>
            <p class="text-sm text-slate-600">
                Closed on {{ $project->closed_at?->format('d M Y') }}. Any unspent project funds were released back to
                unallocated funds. This is a reclassification, not new money.
            </p>
            <p class="mt-2 text-sm text-slate-600">
                Unallocated project funds released: @inr($report['assigned_balance']).
            </p>
        </div>
    @elseif ($project->isClosurePending())
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="mb-1 font-semibold text-amber-900">Pending Closure</h2>
            <p class="text-sm text-amber-800">
                No new spending can be added. Once you are ready, confirm the project as closed.
            </p>
        </div>
    @endif

    @if ($isOwner && $project->isActive())
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <form method="POST" action="{{ route('projects.receipts.store', $project) }}" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
                @csrf
                <h3 class="font-semibold text-slate-900">Record Receipt</h3>
                <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount (₹)" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <input type="date" name="transaction_date" value="{{ $today }}" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="description" placeholder="Description (optional)"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-500">
                    <x-icon name="in" />
                    Record Receipt
                </button>
            </form>

            <form method="POST" action="{{ route('projects.expenses.store', $project) }}" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
                @csrf
                <h3 class="font-semibold text-slate-900">Record Project Expense</h3>
                <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount (₹)" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <select name="category_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Category</option>
                    @foreach ($projectCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="transaction_date" value="{{ $today }}" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="description" placeholder="Description (optional)"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-rose-600 px-3 py-2 text-sm font-medium text-white hover:bg-rose-500">
                    <x-icon name="out" />
                    Record Expense
                </button>
            </form>

            <form method="POST" action="{{ route('projects.transfers.store', $project) }}" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
                @csrf
                <h3 class="font-semibold text-slate-900">Contractor Payment</h3>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" placeholder="Amount (₹)" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <select name="category_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Category</option>
                    @foreach ($projectCategories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="contractor_id" id="payment_contractor_select" required
                        onchange="bamToggleContractorMode(this.value)"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($contractors as $contractor)
                        <option value="{{ $contractor->id }}" @selected(old('contractor_id', $project->contractor_id) == $contractor->id)>
                            {{ $contractor->name }}{{ $contractor->type?->name ? ' ('.$contractor->type->name.')' : '' }}
                        </option>
                    @endforeach
                    <option value="{{ $newContractorSentinel }}" @selected(old('contractor_id') === $newContractorSentinel)>+ Add new contractor</option>
                </select>
                <div id="new-contractor-fields"
                     class="{{ old('contractor_id') === $newContractorSentinel ? '' : 'hidden' }} space-y-2.5 rounded-lg bg-slate-50 p-3.5 ring-1 ring-inset ring-slate-200">
                    <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">New contractor</span>
                    <input type="text" name="new_contractor_name" value="{{ old('new_contractor_name') }}" placeholder="Name"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <input type="text" name="new_contractor_phone" value="{{ old('new_contractor_phone') }}" placeholder="Phone"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <p class="text-xs text-slate-400">Work type can be set later on the Contractors page.</p>
                </div>
                <input type="date" name="transaction_date" value="{{ old('transaction_date', $today) }}" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="description" value="{{ old('description') }}" placeholder="Description (optional)"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    <x-icon name="transfer" />
                    Record Payment
                </button>
            </form>

            <form method="POST" action="{{ route('projects.fund', $project) }}" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
                @csrf
                <h3 class="font-semibold text-slate-900">Allocate Funds to Project</h3>
                <p class="text-xs text-slate-500">Move unallocated funds into this project. Unallocated Funds available: @inr($unallocated).</p>
                <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount (₹)" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <input type="date" name="transaction_date" value="{{ $today }}" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="description" placeholder="Description (optional)"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    <x-icon name="fund" />
                    Allocate Funds
                </button>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Transaction History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Contractor</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $transaction)
                        <tr class="align-top hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->transaction_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium {{ $transaction->type->textColorClass() }}">
                                {{ $transaction->type->label() }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->contractor?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->description ?: '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium {{ $transaction->type->textColorClass() }}">@inr($transaction->amount)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function bamToggleContractorMode(value) {
            var isNew = value === @js($newContractorSentinel);
            document.getElementById('new-contractor-fields').classList.toggle('hidden', !isNew);
        }

        document.addEventListener('DOMContentLoaded', function () {
            var select = document.getElementById('payment_contractor_select');
            if (select) {
                bamToggleContractorMode(select.value);
            }
        });
    </script>
@endsection
