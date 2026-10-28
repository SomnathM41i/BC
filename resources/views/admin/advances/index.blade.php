@extends('layouts.admin')

@section('title', 'Advances')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Employee Advances</h1>
            <p class="text-sm text-slate-500">Track advances and repayments.</p>
        </div>
        <a href="{{ route('admin.advances.create') }}" class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
            Record Advance
        </a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Employee</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($advances as $adv)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-900">{{ $adv->advance_date->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-slate-900">
                                <a href="{{ route('admin.employees.show', $adv->employee) }}" class="hover:underline">{{ $adv->employee->full_name }} ({{ $adv->employee->employee_id }})</a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">@inr($adv->amount)</td>
                            <td class="px-4 py-3 text-slate-600">{{ $adv->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">No advances recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $advances->links() }}
        </div>
    </div>
@endsection
