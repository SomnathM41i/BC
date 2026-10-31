@extends('layouts.admin')

@section('title', 'Pay Salary')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Pay Salary</h1>
            <p class="text-sm text-slate-500">Weekly salary for the last completed week ending on the employee's payment day. Attendance must be marked before payment.</p>
        </div>
        @if($employee)
            <a href="{{ route('admin.attendance.index', ['employee_id' => $employee->id, 'reference_date' => $referenceDate]) }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Open Attendance Chart</a>
        @endif
    </div>

    <div class="mb-6 rounded-xl bg-white shadow-sm">
        <form method="POST" action="{{ route('admin.pay-salary.calculate') }}" class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-end">
            @csrf
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium text-slate-700">Employee</label>
                <select name="employee_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected((string)$selectedEmployeeId === (string)$emp->id)>{{ $emp->full_name }} ({{ $emp->employee_id }}) - {{ ucfirst($emp->weekly_salary_payment_day) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Reference Date</label>
                <input type="date" name="reference_date" value="{{ $referenceDate }}" required class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Calculate</button>
        </form>
    </div>

    @if($employee && $summary && $period)
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Period</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ \Carbon\Carbon::parse($summary['period_start'])->format('d-m-Y') }} - {{ \Carbon\Carbon::parse($summary['period_end'])->format('d-m-Y') }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Present Days</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $summary['present_days'] }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Gross Salary</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">@inr($summary['gross_salary'])</p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Outstanding Advance</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">@inr($summary['outstanding_advance'])</p>
            </div>
        </div>

        @if($currentSummary && ! $currentSummary['already_paid'] && ! $currentWeekEnded && ! \App\Support\Money::isZero($currentSummary['gross_salary']))
            <div class="mb-6 rounded-xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm text-sky-900">
                <p class="font-medium">
                    Current week ({{ \Carbon\Carbon::parse($currentSummary['period_start'])->format('d-m-Y') }} - {{ \Carbon\Carbon::parse($currentSummary['period_end'])->format('d-m-Y') }}):
                    {{ $currentSummary['present_days'] }} present days, @inr($currentSummary['gross_salary']) gross so far.
                </p>
                <p class="mt-1">
                    This week is still in progress. It ends on {{ \Carbon\Carbon::parse($currentSummary['period_end'])->format('d-m-Y') }} and becomes payable after that date.
                    <a href="{{ route('admin.attendance.index', ['employee_id' => $employee->id, 'reference_date' => $referenceDate]) }}" class="font-medium underline">Open current week chart</a>
                </p>
            </div>
        @endif

        @if($summary['already_paid'])
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                This salary period has already been paid. You cannot pay the same period again.
            </div>
        @elseif(\App\Support\Money::isZero($summary['gross_salary']))
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                <p class="font-medium">No attendance is marked for this salary week.</p>
                <p class="mt-1">
                    {{ \Carbon\Carbon::parse($summary['period_start'])->format('d-m-Y') }} - {{ \Carbon\Carbon::parse($summary['period_end'])->format('d-m-Y') }}
                    has no amounts, so there is nothing to pay. Mark attendance for this week first; a ₹0 payment is not recorded.
                </p>
                <a href="{{ route('admin.attendance.index', ['employee_id' => $employee->id, 'reference_date' => $summary['period_end']]) }}" class="mt-2 inline-block font-medium underline">Mark attendance for this week</a>
            </div>
        @else
            <div class="max-w-2xl rounded-xl bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-900">Payment Details</h2>
                </div>
                <form method="POST" action="{{ route('admin.pay-salary.store') }}" id="salary-payment-form"
                      data-gross="{{ $summary['gross_salary'] }}"
                      data-outstanding="{{ $summary['outstanding_advance'] }}"
                      class="space-y-4 px-6 py-6">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    <input type="hidden" name="period_start" value="{{ $summary['period_start'] }}">
                    <input type="hidden" name="period_end" value="{{ $summary['period_end'] }}">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Gross Salary</label>
                            <input type="text" value="@inr($summary['gross_salary'])" disabled class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Outstanding Advance</label>
                            <input type="text" value="@inr($summary['outstanding_advance'])" disabled class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Advance Repayment (₹)</label>
                            <input type="number" step="0.01" min="0" name="advance_repayment" value="{{ old('advance_repayment', '0.00') }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <p class="mt-1 text-xs text-slate-500">Net Salary Payable = Gross - Advance Repayment</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Amount Actually Paid (₹)</label>
                            <input type="number" step="0.01" min="0" name="amount_paid" value="{{ old('amount_paid') }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        <div class="flex flex-wrap gap-x-8 gap-y-1">
                            <span>Net Salary Payable: <strong id="ps-net">@inr($summary['gross_salary'])</strong></span>
                            <span>Remaining Salary Due: <strong id="ps-remaining">@inr($summary['gross_salary'])</strong></span>
                        </div>
                        <p id="ps-warning" class="mt-1 hidden text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Payment Date</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Notes (optional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Record Payment</button>
                        <a href="{{ route('admin.employees.show', $employee) }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
                    </div>
                </form>

                <script>
                    const form = document.getElementById('salary-payment-form');

                    if (form) {
                        const gross = parseFloat(form.dataset.gross || '0');
                        const outstanding = parseFloat(form.dataset.outstanding || '0');
                        const repayInput = form.querySelector('[name="advance_repayment"]');
                        const paidInput = form.querySelector('[name="amount_paid"]');
                        const netEl = document.getElementById('ps-net');
                        const dueEl = document.getElementById('ps-remaining');
                        const warningEl = document.getElementById('ps-warning');

                        const inr = value => '₹' + value.toLocaleString('en-IN', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        });

                        const netFor = repay => Math.max(gross - Math.max(repay, 0), 0);
                        let lastNet = netFor(parseFloat(repayInput.value || '0'));

                        if (paidInput.value === '') {
                            paidInput.value = lastNet.toFixed(2);
                        }

                        const paint = () => {
                            const repay = Math.max(parseFloat(repayInput.value || '0'), 0);
                            const net = netFor(repay);
                            const paid = Math.max(parseFloat(paidInput.value || '0'), 0);
                            const remaining = Math.max(net - paid, 0);

                            netEl.textContent = inr(net);
                            dueEl.textContent = inr(remaining);

                            if (repay > outstanding) {
                                warningEl.textContent = 'Repayment cannot exceed the outstanding advance of ' + inr(outstanding) + '.';
                                warningEl.classList.remove('hidden');
                            } else if (paid > net) {
                                warningEl.textContent = 'Amount paid cannot exceed the net salary payable of ' + inr(net) + '.';
                                warningEl.classList.remove('hidden');
                            } else {
                                warningEl.classList.add('hidden');
                            }
                        };

                        repayInput.addEventListener('input', () => {
                            const net = netFor(parseFloat(repayInput.value || '0'));
                            const paid = parseFloat(paidInput.value || '0');

                            // Keep the paid amount in step with the net unless the
                            // admin has typed their own partial amount.
                            if (paidInput.value === '' || Math.abs(paid - lastNet) < 0.005) {
                                paidInput.value = net.toFixed(2);
                            }

                            lastNet = net;
                            paint();
                        });

                        paidInput.addEventListener('input', paint);
                        paint();
                    }
                </script>
            </div>
        @endif
    @endif
@endsection
