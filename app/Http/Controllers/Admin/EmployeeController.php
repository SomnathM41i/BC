<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Employee::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            if (in_array($status, ['active', 'inactive'], true)) {
                $query->where('status', $status);
            }
        }

        $employees = $query->orderBy('full_name')->paginate(20)->withQueryString();

        return view('admin.employees.index', [
            'employees' => $employees,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.employees.create');
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['employee_id'] = Employee::generateEmployeeId();

        $employee = Employee::create($data);

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('status', 'Employee added successfully.');
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'attendances' => fn ($q) => $q->orderByDesc('attendance_date')->limit(30),
            'advances' => fn ($q) => $q->orderByDesc('advance_date'),
            'salaryPayments' => fn ($q) => $q->orderByDesc('payment_date'),
        ]);

        return view('admin.employees.show', [
            'employee' => $employee,
            'totalAdvances' => $employee->totalAdvances(),
            'totalRepayments' => $employee->totalRepayments(),
            'outstandingAdvance' => $employee->outstandingAdvance(),
        ]);
    }

    public function edit(Employee $employee): View
    {
        return view('admin.employees.edit', [
            'employee' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('status', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        // Do not delete; keep history. Just deactivate if still active.
        if ($employee->status === 'active') {
            $employee->update(['status' => 'inactive']);

            return redirect()->route('admin.employees.index')->with('status', 'Employee deactivated.');
        }

        return redirect()->route('admin.employees.index')->with('status', 'Employee already inactive.');
    }
}
