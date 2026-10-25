@extends('layouts.app')

@section('title', 'Contractors')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Contractors</h1>
        <p class="text-sm text-slate-500">
            Every contractor and the total paid to them. Open a contractor to see the project-by-project breakdown.
        </p>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Contractor Directory</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Trade</th>
                        <th class="px-4 py-3 text-right">Total Given</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($contractors as $contractor)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('contractors.show', $contractor) }}"
                                   class="text-slate-900 underline decoration-slate-300 hover:decoration-slate-500">
                                    {{ $contractor->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $contractor->phone ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $contractor->type?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-slate-900">@inr($totals[$contractor->id] ?? '0.00')</td>
                            <td class="px-4 py-3">
                                @if ($contractor->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No contractors yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
