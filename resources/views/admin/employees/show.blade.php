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

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Advances</p>
            <p class="mt-2 text-xl font-semibold text-slate-900">@inr($totalAdvances)</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Repayments</p>
            <p class="mt-2 text-xl font-semibold text-slate-900">@inr($totalRepayments)</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Outstanding Advance</p>
            <p class="mt-2 text-xl font-semibold text-slate-900">@inr($outstandingAdvance)</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Daily Salary</p>
            <p class="mt-2 text-xl font-semibold text-slate-900">@inr($employee->daily_salary)</p>
        </div>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Profile</h2>
            </div>
            <dl class="divide-y divide-slate-100 text-sm">
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-slate-500">Full Name</dt>
                    <dd class="text-slate-900">{{ $employee->full_name }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-slate-500">Phone Number</dt>
                    <dd class="text-slate-900">{{ $employee->phone_number }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-slate-500">Daily Salary</dt>
                    <dd class="text-slate-900">@inr($employee->daily_salary)</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-slate-500">Weekly Salary Payment Day</dt>
                    <dd class="text-slate-900">{{ ucfirst($employee->weekly_salary_payment_day) }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-slate-500">Joining Date</dt>
                    <dd class="text-slate-900">{{ $employee->joining_date->format('d-m-Y') }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-slate-500">Status</dt>
                    <dd class="text-slate-900">{{ ucfirst($employee->status) }}</dd>
                </div>
            </dl>
        </div>
        <div class="rounded-xl bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Attendance (Recent)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Payable Days</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employee->attendances as $att)
                            <tr>
                                <td class="px-4 py-3 text-slate-900">{{ $att->attendance_date->format('d-m-Y') }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ str_replace('_', ' ', ucfirst($att->status)) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $att->payable_days }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-500">No attendance records.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Advances</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employee->advances as $adv)
                            <tr>
                                <td class="px-4 py-3 text-slate-900">{{ $adv->advance_date->format('d-m-Y') }}</td>
                                <td class="px-4 py-3 text-slate-600">@inr($adv->amount)</td>
                                <td class="px-4 py-3 text-slate-600">{{ $adv->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-500">No advances.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rounded-xl bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Salary History</h2>
            </div>
            <div class="overflow-x-auto">
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
                        @forelse ($employee->salaryPayments as $pay)
                            <tr>
                                <td class="px-4 py-3 text-slate-900">{{ $pay->period_start->format('d-m-Y') }} - {{ $pay->period_end->format('d-m-Y') }}</td>
                                <td class="px-4 py-3 text-slate-600">@inr($pay->gross_salary)</td>
                                <td class="px-4 py-3 text-slate-600">@inr($pay->advance_repayment)</td>
                                <td class="px-4 py-3 text-slate-600">@inr($pay->net_salary_payable)</td>
                                <td class="px-4 py-3 text-slate-600">@inr($pay->amount_paid)</td>
                                <td class="px-4 py-3 text-slate-600">@inr($pay->remaining_salary_due)</td>
                                <td class="px-4 py-3 text-slate-600">{{ str_replace('_', ' ', ucfirst($pay->status)) }}</td>
                                <td class="px-4 py-3">
                                    @if(\App\Support\Money::gt($pay->remaining_salary_due, '0'))
                                        <form method="POST" action="{{ route('admin.pay-salary.complete', $pay) }}" class="flex items-center gap-1">
                                            @csrf
                                            <input type="number" step="0.01" min="0.01" max="{{ $pay->remaining_salary_due }}" name="amount"
                                                   placeholder="Amount" required
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
        </div>
    </div>
@endsection
