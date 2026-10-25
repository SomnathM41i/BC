@extends('layouts.app')

@section('title', 'Projects')

@php
    $statusClasses = [
        'active' => 'bg-emerald-100 text-emerald-800',
        'closure_pending' => 'bg-amber-100 text-amber-800',
        'closed' => 'bg-slate-200 text-slate-700',
    ];
@endphp

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Projects</h1>
            <p class="text-sm text-slate-500">Track contract value, funding, spend and status for every project.</p>
        </div>
        <a href="{{ route('admin.projects.create') }}"
           class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            <x-icon name="plus" />
            New Project
        </a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Contractor</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Contract Value</th>
                        <th class="px-4 py-3 text-right">Received</th>
                        <th class="px-4 py-3 text-right">Available Balance</th>
                        <th class="px-4 py-3 text-right">Total Spend</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $row)
                        @php($project = $row['project'])
                        @php($report = $row['report'])
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $project->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $project->location ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $project->contractor?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClasses[$project->status->value] }}">
                                    {{ $project->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">@inr($report['agreed_amount'])</td>
                            <td class="px-4 py-3 text-right">@inr($report['total_received'])</td>
                            <td class="px-4 py-3 text-right font-medium">@inr($report['assigned_balance'])</td>
                            <td class="px-4 py-3 text-right">@inr($report['total_costs'])</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.projects.show', $project) }}" class="inline-flex items-center gap-1 text-slate-700 underline">
                                    <x-icon name="view" class="h-3.5 w-3.5" />
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-slate-500">No projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
