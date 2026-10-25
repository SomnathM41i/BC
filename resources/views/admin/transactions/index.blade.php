@extends('layouts.admin')

@section('title', 'Transactions')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Transactions</h1>
            <p class="text-sm text-slate-500">Complete record of all transactions across the business.</p>
        </div>
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="flex items-end gap-2">
            <div>
                <label for="type" class="mb-1 block text-xs font-medium text-slate-500">Filter by type</label>
                <select id="type" name="type" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected($selectedType === $type->value)>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                <x-icon name="filter" />
                Apply
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Contractor</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Recorded By</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $transaction)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->transaction_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium {{ $transaction->type->textColorClass() }}">{{ $transaction->type->label() }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->project?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->contractor?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->description ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->creator?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium {{ $transaction->type->textColorClass() }}">@inr($transaction->amount)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-slate-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
@endsection
