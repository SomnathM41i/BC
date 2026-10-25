@extends('layouts.app')

@section('title', 'Owner Personal Wallet')

@php($today = now()->toDateString())

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Owner Personal Wallet</h1>
        <p class="text-sm text-slate-500">
            The owner's personal money. Separate from company cash and project spend.
            Personal income and expenses never change the company or project wallets.
        </p>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Personal Wallet Balance</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">@inr($summary['balance'])</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Personal Income</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-600">@inr($summary['income'])</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Personal Expenses</p>
            <p class="mt-1 text-2xl font-semibold text-rose-600">@inr($summary['expenses'])</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Company Balance</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">@inr($businessBalance)</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Unallocated Funds</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">@inr($unallocated)</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Transferred to Company</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">@inr($summary['to_company'])</p>
        </div>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2">
        <form method="POST" action="{{ route('owner.income') }}" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
            @csrf
            <h2 class="font-semibold text-slate-900">Record Personal Income</h2>
            <p class="text-xs text-slate-500">Personal income from any personal source. Stays in the personal wallet only.</p>
            <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount (₹)" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <select name="category_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Category (optional)</option>
                @foreach ($incomeCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <input type="date" name="transaction_date" value="{{ $today }}" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="description" placeholder="Description (optional)"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-500">
                <x-icon name="in" />
                Record Income
            </button>
        </form>

        <form method="POST" action="{{ route('owner.expense') }}" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
            @csrf
            <h2 class="font-semibold text-slate-900">Record Personal Expense</h2>
            <p class="text-xs text-slate-500">Personal spending. Reduces the personal wallet only, never company or project money.</p>
            <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount (₹)" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <select name="category_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Category (optional)</option>
                @foreach ($categories as $category)
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
    </div>

    <h2 class="mb-3 text-lg font-semibold text-slate-900">Company &harr; Personal Transfers</h2>

    <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2">
        <form method="POST" action="{{ route('owner.transfer') }}" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
            @csrf
            <h3 class="font-semibold text-slate-900">Transfer to Owner</h3>
            <p class="text-xs text-slate-500">Move company cash into the personal wallet. Uses unallocated company funds.</p>
            <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount (₹)" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <select name="category_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <input type="date" name="transaction_date" value="{{ $today }}" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="description" placeholder="Description (optional)"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                <x-icon name="wallet" />
                Transfer to Owner
            </button>
        </form>

        <form method="POST" action="{{ route('owner.return') }}" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
            @csrf
            <h3 class="font-semibold text-slate-900">Transfer to Company</h3>
            <p class="text-xs text-slate-500">Move personal money into company cash. Personal wallet available: @inr($summary['balance']).</p>
            <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount (₹)" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <input type="date" name="transaction_date" value="{{ $today }}" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="description" placeholder="Description (optional)"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-500">
                <x-icon name="in" />
                Transfer to Company
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Personal Wallet Activity</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $transaction)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->transaction_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium {{ $transaction->type->textColorClass() }}">{{ $transaction->type->label() }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->description ?: '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium {{ $transaction->type->textColorClass() }}">@inr($transaction->amount)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
