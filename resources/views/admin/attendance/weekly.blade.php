@extends('layouts.admin')

@section('title', 'Weekly Attendance Chart')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Salary Week Attendance Chart</h1>
            <p class="text-sm text-slate-500">7-day chart from salary date to salary date based on employee's weekly payment day.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.attendance.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Daily View</a>
        </div>
    </div>

    <div class="mb-6 rounded-xl bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.attendance.weekly') }}" class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium text-slate-700">Employee</label>
                <select name="employee_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected((string)$employeeId === (string)$emp->id)>{{ $emp->full_name }} ({{ $emp->employee_id }}) - {{ ucfirst($emp->weekly_salary_payment_day) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Reference Date</label>
                <input type="date" name="reference_date" value="{{ $referenceDate }}" required class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Load Week</button>
        </form>
    </div>

    @if($employee && count($dates) > 0)
        @if($beforeJoining)
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                The selected date is before {{ $employee->full_name }}'s joining date ({{ $employee->joining_date->format('d-m-Y') }}). Pick a date on or after the joining date.
            </div>
        @endif
        <form method="POST" action="{{ route('admin.attendance.store') }}" id="weekly-attendance-form">
            @csrf
            <input type="hidden" name="weekly_employee_id" value="{{ $employee->id }}">
            <input type="hidden" name="weekly_reference_date" value="{{ $referenceDate }}">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">{{ $employee->full_name }} ({{ $employee->employee_id }}) - Salary Week: {{ $dates[0]['full'] }} to {{ end($dates)['full'] }} | Payment Day: {{ ucfirst($employee->weekly_salary_payment_day) }}</h2>
                    <p class="mt-1 text-xs text-slate-500">Days before the joining date are locked.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Day</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Status (Present 1.0 / Half 0.5 / Absent 0.0)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($dates as $d)
                                @php($existingStatus = optional($existing->get($d['date']))->status)
                                <tr class="weekly-row {{ $d['before_joining'] ? 'bg-slate-50 text-slate-400' : '' }} {{ $d['is_today'] ? 'border-l-4 border-l-emerald-500' : '' }}">
                                    <td class="px-4 py-3 font-medium">
                                        {{ $d['label'] }}
                                        @if($d['is_today'])
                                            <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Today</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $d['full'] }}</td>
                                    <td class="px-4 py-3">
                                        @if($d['before_joining'])
                                            <span class="text-xs text-slate-400">Before joining</span>
                                        @else
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                                <label class="inline-flex cursor-pointer items-center gap-1.5 text-sm text-slate-600">
                                                    <input type="radio" name="weekly[{{ $employee->id }}][{{ $d['date'] }}]" value=""
                                                           class="weekly-status h-4 w-4 border-slate-300"
                                                           @checked(! $existingStatus)>
                                                    Not Marked
                                                </label>
                                                <label class="inline-flex cursor-pointer items-center gap-1.5 text-sm text-slate-700">
                                                    <input type="radio" name="weekly[{{ $employee->id }}][{{ $d['date'] }}]" value="present"
                                                           class="weekly-status h-4 w-4 accent-emerald-600"
                                                           @checked($existingStatus === 'present')>
                                                    Present (1.0)
                                                </label>
                                                <label class="inline-flex cursor-pointer items-center gap-1.5 text-sm text-slate-700">
                                                    <input type="radio" name="weekly[{{ $employee->id }}][{{ $d['date'] }}]" value="half_day"
                                                           class="weekly-status h-4 w-4 accent-amber-500"
                                                           @checked($existingStatus === 'half_day')>
                                                    Half Day (0.5)
                                                </label>
                                                <label class="inline-flex cursor-pointer items-center gap-1.5 text-sm text-slate-700">
                                                    <input type="radio" name="weekly[{{ $employee->id }}][{{ $d['date'] }}]" value="absent"
                                                           class="weekly-status h-4 w-4 accent-red-600"
                                                           @checked($existingStatus === 'absent')>
                                                    Absent (0.0)
                                                </label>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right font-medium text-slate-700">Totals</td>
                                <td class="px-4 py-3 text-sm text-slate-900">
                                    Present: <span id="w-present">0</span> | Absent: <span id="w-absent">0</span> | Half Day: <span id="w-half">0</span> | Total Payable Days: <span id="w-total" class="font-semibold">0.0</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">
                    <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save Weekly Attendance</button>
                </div>
            </div>
        </form>

        <script>
            const weeklyPayable = { present: 1, half_day: 0.5, absent: 0 };

            function recalcWeekly() {
                let present = 0, absent = 0, half = 0, total = 0;
                document.querySelectorAll('.weekly-status:checked').forEach(radio => {
                    if (radio.value === 'present') { present++; total += weeklyPayable.present; }
                    else if (radio.value === 'absent') { absent++; total += weeklyPayable.absent; }
                    else if (radio.value === 'half_day') { half++; total += weeklyPayable.half_day; }
                });
                document.getElementById('w-present').textContent = present;
                document.getElementById('w-absent').textContent = absent;
                document.getElementById('w-half').textContent = half;
                document.getElementById('w-total').textContent = total.toFixed(1);
            }
            document.addEventListener('DOMContentLoaded', recalcWeekly);
            document.getElementById('weekly-attendance-form')?.addEventListener('change', recalcWeekly);
        </script>
    @elseif($employeeId)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            No employees found or unable to load week.
        </div>
    @else
        <div class="rounded-xl border border-slate-200 bg-white px-5 py-6 text-center text-sm text-slate-500">
            Select an employee and reference date to view the 7-day salary week chart.
        </div>
    @endif
@endsection
