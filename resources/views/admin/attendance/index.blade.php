@extends('layouts.admin')

@section('title', 'Attendance')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Attendance</h1>
        <p class="text-sm text-slate-500">Select an employee and mark each day's work level. The amount comes from the employee's normal day salary.</p>
    </div>

    <div class="mb-6 rounded-xl bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium text-slate-700">Employee</label>
                <select name="employee_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected((string)$employeeId === (string)$emp->id)>{{ $emp->full_name }} ({{ $emp->employee_id }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Date</label>
                <input type="date" name="reference_date" value="{{ $referenceDate }}" required class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Load Week</button>
        </form>
    </div>

    @if($employee && count($dates) > 0)
        <form method="POST" action="{{ route('admin.attendance.store') }}" id="attendance-form">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <input type="hidden" name="reference_date" value="{{ $referenceDate }}">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">{{ $employee->full_name }} ({{ $employee->employee_id }})</h2>
                    <p class="mt-1 text-xs text-slate-500">Salary week {{ $dates[0]['full'] }} to {{ end($dates)['full'] }}. Payment day: {{ ucfirst($employee->weekly_salary_payment_day) }}.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Day</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Work Level</th>
                                <th class="px-4 py-3">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($dates as $d)
                                @php($row = $existing->get($d['date']))
                                @php($locked = $d['before_joining'] || $d['paid'])
                                <tr class="day-row {{ $locked ? 'bg-slate-50 text-slate-400' : '' }} {{ $d['is_today'] ? 'border-l-4 border-l-emerald-500' : '' }}">
                                    <td class="px-4 py-3 font-medium">
                                        {{ $d['label'] }}
                                        @if($d['is_today'])
                                            <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Today</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $d['full'] }}</td>
                                    @if($d['before_joining'])
                                        <td colspan="2" class="px-4 py-3 text-xs text-slate-400">Before joining</td>
                                    @elseif($d['paid'])
                                        <td class="px-4 py-3 text-xs text-slate-500">Salary paid, locked{{ $row ? ': '.(\App\Support\WorkLevel::LABELS[$row->work_level] ?? ucfirst($row->status)) : '' }}</td>
                                        <td class="px-4 py-3 text-xs text-slate-500">
                                            @if($row) @inr($row->amount) @endif
                                        </td>
                                    @else
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                                {{-- An empty value leaves the day as it is, so this option only exists
                                                     for a day that has no level to choose: unmarked or an older entry. --}}
                                                @if(! $row)
                                                    <label class="inline-flex cursor-pointer items-center gap-1.5 text-sm text-slate-600">
                                                        <input type="radio" name="days[{{ $d['date'] }}][level]" value="" data-amount="0" class="day-level h-4 w-4 border-slate-300" checked>
                                                        Not Marked
                                                    </label>
                                                @elseif($row->status === 'present' && ! $row->work_level)
                                                    <label class="inline-flex cursor-pointer items-center gap-1.5 text-sm text-slate-700">
                                                        <input type="radio" name="days[{{ $d['date'] }}][level]" value="" data-amount="{{ $row->amount }}" data-count="present" class="day-level h-4 w-4 accent-emerald-600" checked>
                                                        Present (older entry)
                                                    </label>
                                                @endif
                                                @foreach($amounts as $level => $amount)
                                                    {{-- A marked day keeps the amount it was saved with, even if the salary changed. --}}
                                                    @php($marked = $row?->work_level === $level)
                                                    @php($amount = $marked ? $row->amount : $amount)
                                                    @php($unset = \App\Support\Money::isZero($amount))
                                                    <label @class(['inline-flex items-center gap-1.5 text-sm', 'cursor-pointer text-slate-700' => ! $unset, 'text-slate-400' => $unset])>
                                                        <input type="radio" name="days[{{ $d['date'] }}][level]" value="{{ $level }}" data-amount="{{ $amount }}" data-count="{{ $level }}"
                                                               class="day-level h-4 w-4 accent-emerald-600" @checked($marked) @disabled($unset)>
                                                        {{ \App\Support\WorkLevel::LABELS[$level] }}
                                                        <span class="text-xs text-slate-500">{{ $unset ? 'No salary set' : \App\Support\Money::inr($amount) }}</span>
                                                    </label>
                                                @endforeach
                                                <label class="inline-flex cursor-pointer items-center gap-1.5 text-sm text-slate-700">
                                                    <input type="radio" name="days[{{ $d['date'] }}][level]" value="absent" data-amount="0" data-count="absent" class="day-level h-4 w-4 accent-red-600" @checked($row?->status === 'absent')>
                                                    Absent
                                                </label>
                                            </div>
                                        </td>
                                        <td class="day-amount whitespace-nowrap px-4 py-3 text-slate-700"></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right font-medium text-slate-700">Totals</td>
                                <td colspan="2" class="px-4 py-3 text-sm text-slate-900">
                                    Half: <span id="w-half_day">0</span> | Normal: <span id="w-normal_day">0</span> | Long: <span id="w-long_day">0</span> | Absent: <span id="w-absent">0</span> | Week Total: <span id="w-total" class="font-semibold">₹0.00</span>
                                    <span id="w-older-wrap" class="hidden"> | Older entries: <span id="w-present">0</span></span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">
                    <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save Attendance</button>
                </div>
            </div>
        </form>

        <script>
            const inr = value => '₹' + value.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            function recalcWeek() {
                const counts = { half_day: 0, normal_day: 0, long_day: 0, absent: 0, present: 0 };
                let total = 0;

                document.querySelectorAll('.day-row').forEach(row => {
                    const level = row.querySelector('.day-level:checked');

                    if (! level) { return; }

                    const amount = parseFloat(level.dataset.amount || '0');

                    row.querySelector('.day-amount').textContent = level.dataset.count ? inr(amount) : '—';
                    total += amount;

                    if (level.dataset.count) { counts[level.dataset.count]++; }
                });

                Object.entries(counts).forEach(([key, count]) => {
                    document.getElementById('w-' + key).textContent = count;
                });
                document.getElementById('w-older-wrap').classList.toggle('hidden', counts.present === 0);
                document.getElementById('w-total').textContent = inr(total);
            }

            document.addEventListener('DOMContentLoaded', recalcWeek);
            document.getElementById('attendance-form')?.addEventListener('input', recalcWeek);
        </script>
    @elseif($employeeId)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            Unable to load this employee.
        </div>
    @else
        <div class="rounded-xl border border-slate-200 bg-white px-5 py-6 text-center text-sm text-slate-500">
            Select an employee to mark attendance.
        </div>
    @endif
@endsection
