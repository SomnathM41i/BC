@extends('layouts.statement')

@section('title', $contractor->name)

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">{{ $contractor->name }}</h1>
        @if ($contractor->type?->name)
            <p class="text-sm text-slate-500">{{ $contractor->type->name }}</p>
        @endif
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Given</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">@inr($report['total'])</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Projects</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">{{ count($report['rows']) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Payments by Project</h2>
            <p class="mt-1 text-xs text-slate-500">How much has been given to {{ $contractor->name }} for each project.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3 text-center">Payments</th>
                        <th class="px-4 py-3">Last Payment</th>
                        <th class="px-4 py-3 text-right">Amount Given</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($report['rows'] as $row)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $row['project']->name }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $row['payments'] }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $row['last_date'] ? \Illuminate\Support\Carbon::parse($row['last_date'])->format('d M Y') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-slate-900">@inr($row['total'])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                No projects or payments recorded for this contractor yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if (count($report['rows']))
                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Total Given
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">@inr($report['total'])</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <p class="mt-6 text-xs text-slate-400">This is a read-only statement generated automatically.</p>
@endsection
