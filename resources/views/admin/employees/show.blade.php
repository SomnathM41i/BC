@extends('layouts.admin')

@section('title', $employee->full_name)

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $employee->full_name }}</h1>
            <p class="text-sm text-slate-500">{{ $employee->employee_id }} · {{ ucfirst($employee->status) }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.employees.edit', $employee) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Edit</a>
            <a href="{{ route('admin.attendance.weekly', ['employee_id' => $employee->id, 'reference_date' => now()->toDateString()]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Mark Attendance</a>
            <a href="{{ route('admin.advances.create', ['employee_id' => $employee->id]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Add Advance</a>
            <a href="{{ route('admin.pay-salary.create', ['employee_id' => $employee->id]) }}" class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Pay Salary</a>
            <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}">
                @csrf
                @method('DELETE')
                <button class="rounded-md border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50">Deactivate</button>
            </form>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow-sm sm:p-5">
            <p class="text-xs text-slate-500 sm:text-sm">Total Advances</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 sm:text-xl">@inr($totalAdvances)</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm sm:p-5">
            <p class="text-xs text-slate-500 sm:text-sm">Total Repayments</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 sm:text-xl">@inr($totalRepayments)</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm sm:p-5">
            <p class="text-xs text-slate-500 sm:text-sm">Outstanding Advance</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 sm:text-xl">@inr($outstandingAdvance)</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm sm:p-5">
            <p class="text-xs text-slate-500 sm:text-sm">Daily Salary</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 sm:text-xl">@inr($employee->daily_salary)</p>
        </div>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <div class="flex h-full flex-col rounded-xl bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-900">Profile</h2>
            </div>
            <dl class="divide-y divide-slate-100 text-sm">
                <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4 sm:px-5">
                    <dt class="text-slate-500">Full Name</dt>
                    <dd class="text-slate-900 sm:text-right">{{ $employee->full_name }}</dd>
                </div>
                <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4 sm:px-5">
                    <dt class="text-slate-500">Phone Number</dt>
                    <dd class="text-slate-900 sm:text-right">{{ $employee->phone_number }}</dd>
                </div>
                <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4 sm:px-5">
                    <dt class="text-slate-500">Daily Salary</dt>
                    <dd class="text-slate-900 sm:text-right">@inr($employee->daily_salary)</dd>
                </div>
                <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4 sm:px-5">
                    <dt class="text-slate-500">Weekly Salary Payment Day</dt>
                    <dd class="text-slate-900 sm:text-right">{{ ucfirst($employee->weekly_salary_payment_day) }}</dd>
                </div>
                <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4 sm:px-5">
                    <dt class="text-slate-500">Joining Date</dt>
                    <dd class="text-slate-900 sm:text-right">{{ $employee->joining_date->format('d-m-Y') }}</dd>
                </div>
                <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4 sm:px-5">
                    <dt class="text-slate-500">Status</dt>
                    <dd class="text-slate-900 sm:text-right">{{ ucfirst($employee->status) }}</dd>
                </div>
            </dl>
        </div>

        <div class="flex h-full flex-col rounded-xl bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-900">Attendance</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 sm:px-4">Date</th>
                            <th class="px-3 py-2 sm:px-4">Status</th>
                            <th class="px-3 py-2 sm:px-4">Payable Days</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($attendances as $att)
                            <tr>
                                <td class="whitespace-nowrap px-3 py-2 text-slate-900 sm:px-4">{{ $att->attendance_date->format('d-m-Y') }}</td>
                                <td class="px-3 py-2 text-slate-600 sm:px-4">{{ str_replace('_', ' ', ucfirst($att->status)) }}</td>
                                <td class="px-3 py-2 text-slate-600 sm:px-4">{{ $att->payable_days }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-500">No attendance records.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($attendances->hasPages())
                <div class="mt-auto border-t border-slate-100 px-4 py-3 sm:px-5">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="mb-6 rounded-xl bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-900">Advances</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2 sm:px-4 sm:py-3">Date</th>
                        <th class="px-3 py-2 sm:px-4 sm:py-3">Amount</th>
                        <th class="px-3 py-2 sm:px-4 sm:py-3">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($advances as $adv)
                        <tr>
                            <td class="whitespace-nowrap px-3 py-2 text-slate-900 sm:px-4 sm:py-3">{{ $adv->advance_date->format('d-m-Y') }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-slate-600 sm:px-4 sm:py-3">@inr($adv->amount)</td>
                            <td class="break-words px-3 py-2 text-slate-600 sm:px-4 sm:py-3">{{ $adv->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-slate-500">No advances.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($advances->hasPages())
            <div class="border-t border-slate-100 px-4 py-3 sm:px-5">
                {{ $advances->links() }}
            </div>
        @endif
    </div>

    <div class="rounded-xl bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-900">Salary History</h2>
        </div>

        {{-- Stacked cards for phones and tablets; the full table needs a wide screen. --}}
        <div class="divide-y divide-slate-100 xl:hidden">
            @forelse ($salaryPayments as $pay)
                <div class="px-4 py-4 sm:px-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-medium text-slate-900">{{ $pay->period_start->format('d-m-Y') }} - {{ $pay->period_end->format('d-m-Y') }}</p>
                        @if ($pay->status === 'paid')
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Paid</span>
                        @else
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Partially Paid</span>
                        @endif
                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="text-xs text-slate-500">Gross</dt>
                            <dd class="text-slate-900">@inr($pay->gross_salary)</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Advance Repayment</dt>
                            <dd class="text-slate-900">@inr($pay->advance_repayment)</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Net Payable</dt>
                            <dd class="text-slate-900">@inr($pay->net_salary_payable)</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Amount Paid</dt>
                            <dd class="text-slate-900">@inr($pay->amount_paid)</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Remaining Due</dt>
                            <dd class="font-medium text-slate-900">@inr($pay->remaining_salary_due)</dd>
                        </div>
                    </dl>

                    @if (\App\Support\Money::gt($pay->remaining_salary_due, '0'))
                        <form method="POST" action="{{ route('admin.pay-salary.complete', $pay) }}" class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                            @csrf
                            <input type="number" step="0.01" min="0.01" max="{{ $pay->remaining_salary_due }}" name="amount"
                                   placeholder="Amount to pay" required aria-label="Amount to pay"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm sm:w-40">
                            <input type="hidden" name="payment_date" value="{{ now()->toDateString() }}">
                            <button class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Pay Balance</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="px-4 py-6 text-center text-sm text-slate-500 sm:px-5">No salary payments.</p>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto xl:block">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Period</th>
                        <th class="px-4 py-3">Gross</th>
                        <th class="px-4 py-3">Advance Repayment</th>
                        <th class="px-4 py-3">Net Payable</th>
                        <th class="px-4 py-3">Amount Paid</th>
                        <th class="px-4 py-3">Remaining Due</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Pay Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($salaryPayments as $pay)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-900">{{ $pay->period_start->format('d-m-Y') }} - {{ $pay->period_end->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">@inr($pay->gross_salary)</td>
                            <td class="px-4 py-3 text-slate-600">@inr($pay->advance_repayment)</td>
                            <td class="px-4 py-3 text-slate-600">@inr($pay->net_salary_payable)</td>
                            <td class="px-4 py-3 text-slate-600">@inr($pay->amount_paid)</td>
                            <td class="px-4 py-3 text-slate-600">@inr($pay->remaining_salary_due)</td>
                            <td class="px-4 py-3 text-slate-600">{{ str_replace('_', ' ', ucfirst($pay->status)) }}</td>
                            <td class="px-4 py-3">
                                @if (\App\Support\Money::gt($pay->remaining_salary_due, '0'))
                                    <form method="POST" action="{{ route('admin.pay-salary.complete', $pay) }}" class="flex items-center gap-1">
                                        @csrf
                                        <input type="number" step="0.01" min="0.01" max="{{ $pay->remaining_salary_due }}" name="amount"
                                               placeholder="Amount" required aria-label="Amount to pay"
                                               class="w-24 rounded-md border border-slate-300 px-2 py-1 text-xs">
                                        <input type="hidden" name="payment_date" value="{{ now()->toDateString() }}">
                                        <button class="rounded-md bg-slate-900 px-2 py-1 text-xs font-medium text-white hover:bg-slate-800">Pay</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-slate-500">No salary payments.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($salaryPayments->hasPages())
            <div class="border-t border-slate-100 px-4 py-3 sm:px-5">
                {{ $salaryPayments->links() }}
            </div>
        @endif
    </div>
@endsection
