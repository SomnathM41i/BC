@extends('layouts.admin')

@section('title', 'Attendance')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Daily Attendance</h1>
            <p class="text-sm text-slate-500">Mark Present/Absent/Half Day for all employees.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.attendance.weekly') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">View Salary Week Chart (7 Days)</a>
            <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <button class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Load</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.attendance.store') }}" id="daily-attendance-form">
        @csrf
        <input type="hidden" name="attendance_date" value="{{ $date }}">
        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-semibold text-slate-900">Attendance for {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</h2>
                <div class="flex items-center gap-2">
                    <button type="button" id="mark-all-present" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs hover:bg-slate-50">Mark all Present</button>
                    <button type="button" id="clear-all" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs hover:bg-slate-50">Clear all</button>
                </div>
            </div>
            <div class="border-b border-slate-100 bg-slate-50 px-5 py-3 text-sm text-slate-700">
                Present: <span id="d-present" class="font-semibold">0</span>
                | Absent: <span id="d-absent" class="font-semibold">0</span>
                | Half Day: <span id="d-half" class="font-semibold">0</span>
                | Not Marked: <span id="d-unmarked" class="font-semibold">0</span>
                | Total Payable Days: <span id="d-total" class="font-semibold">0.0</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Employee</th>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employees as $employee)
                            @php($existingStatus = optional($existing->get($employee->id))->status)
                            @php($locked = in_array($employee->id, $lockedEmployeeIds, true))
                            <tr class="{{ $locked ? 'bg-slate-50 text-slate-400' : '' }}">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $employee->full_name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $employee->employee_id }}</td>
                                <td class="px-4 py-3">
                                    @if($locked)
                                        <span class="text-xs text-slate-400">Before joining ({{ $employee->joining_date->format('d-m-Y') }})</span>
                                    @else
                                        <input type="hidden" name="attendance[{{ $loop->index }}][employee_id]" value="{{ $employee->id }}">
                                        <select name="attendance[{{ $loop->index }}][status]" class="daily-status rounded-md border border-slate-300 px-3 py-2 text-sm">
                                            <option value="" @selected(! $existingStatus)>Not Marked</option>
                                            <option value="present" @selected($existingStatus === 'present')>Present (1.0)</option>
                                            <option value="half_day" @selected($existingStatus === 'half_day')>Half Day (0.5)</option>
                                            <option value="absent" @selected($existingStatus === 'absent')>Absent (0.0)</option>
                                        </select>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-500">No employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->count() > 0)
                <div class="border-t border-slate-100 px-5 py-4">
                    <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save Attendance</button>
                    <p class="mt-1 text-xs text-slate-500">Only employees with a selected status are saved.</p>
                </div>
            @endif
        </div>
    </form>

    <script>
        const payableByStatus = { present: 1, half_day: 0.5, absent: 0 };

        function recalcDaily() {
            let present = 0, absent = 0, half = 0, unmarked = 0, total = 0;
            document.querySelectorAll('.daily-status').forEach(sel => {
                const value = sel.value;
                if (value === 'present') { present++; total += payableByStatus.present; }
                else if (value === 'absent') { absent++; total += payableByStatus.absent; }
                else if (value === 'half_day') { half++; total += payableByStatus.half_day; }
                else { unmarked++; }
            });
            document.getElementById('d-present').textContent = present;
            document.getElementById('d-absent').textContent = absent;
            document.getElementById('d-half').textContent = half;
            document.getElementById('d-unmarked').textContent = unmarked;
            document.getElementById('d-total').textContent = total.toFixed(1);
        }

        document.addEventListener('DOMContentLoaded', () => {
            recalcDaily();

            document.getElementById('daily-attendance-form')?.addEventListener('change', recalcDaily);

            document.getElementById('mark-all-present')?.addEventListener('click', () => {
                document.querySelectorAll('.daily-status').forEach(sel => { sel.value = 'present'; });
                recalcDaily();
            });

            document.getElementById('clear-all')?.addEventListener('click', () => {
                document.querySelectorAll('.daily-status').forEach(sel => { sel.value = ''; });
                recalcDaily();
            });
        });
    </script>
@endsection
