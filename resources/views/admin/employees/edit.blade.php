@extends('layouts.admin')

@section('title', 'Edit Employee')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Edit Employee</h1>
        <p class="text-sm text-slate-500">{{ $employee->employee_id }} · {{ $employee->full_name }}</p>
    </div>

    <div class="max-w-2xl rounded-xl bg-white shadow-sm">
        <form method="POST" action="{{ route('admin.employees.update', $employee) }}" class="space-y-4 px-6 py-6">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Phone Number</label>
                <input type="text" name="phone_number" value="{{ old('phone_number', $employee->phone_number) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Employee Type</label>
                <select name="employee_type_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select Type</option>
                    @foreach($employeeTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) old('employee_type_id', $employee->employee_type_id) === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Weekly Salary Payment Day</label>
                <select name="weekly_salary_payment_day" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                        <option value="{{ $day }}" @selected(old('weekly_salary_payment_day', $employee->weekly_salary_payment_day) === $day)>{{ ucfirst($day) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $employee->joining_date->format('Y-m-d')) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="active" @selected(old('status', $employee->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $employee->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Update</button>
                <a href="{{ route('admin.employees.show', $employee) }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
