@extends('layouts.admin')

@section('title', 'Record Advance')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Record Advance</h1>
        <p class="text-sm text-slate-500">Give advance to an employee.</p>
    </div>

    <div class="max-w-xl rounded-xl bg-white shadow-sm">
        <form method="POST" action="{{ route('admin.advances.store') }}" class="space-y-4 px-6 py-6">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Employee</label>
                <select name="employee_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(old('employee_id', $selected) == $emp->id)>{{ $emp->full_name }} ({{ $emp->employee_id }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Advance Date</label>
                    <input type="date" name="advance_date" value="{{ old('advance_date', now()->toDateString()) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Notes (optional)</label>
                <textarea name="notes" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
            </div>
            <div class="flex items-center gap-3">
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save</button>
                <a href="{{ route('admin.advances.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
