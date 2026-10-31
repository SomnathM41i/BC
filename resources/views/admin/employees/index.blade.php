@extends('layouts.admin')

@section('title', 'Employees')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Employees</h1>
            <p class="text-sm text-slate-500">Manage employees, attendance, advances and salary.</p>
        </div>
        <a href="{{ route('admin.employees.create') }}" class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
            Add Employee
        </a>
    </div>

    <div class="mb-4 rounded-xl bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.employees.index') }}" class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, ID or phone" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm sm:max-w-xs">
            <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
            <button class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Filter</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Employee ID</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Weekly Payment Day</th>
                        <th class="px-4 py-3">Joining Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $employee->employee_id }}</td>
                            <td class="px-4 py-3 text-slate-900">{{ $employee->full_name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $employee->phone_number }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $employee->type?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ ucfirst($employee->weekly_salary_payment_day) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $employee->joining_date->format('d-m-Y') }}</td>
                            <td class="px-4 py-3">
                                @if ($employee->status === 'active')
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.employees.show', $employee) }}" class="text-slate-600 hover:text-slate-900">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-slate-500">No employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $employees->links() }}
        </div>
    </div>
@endsection
