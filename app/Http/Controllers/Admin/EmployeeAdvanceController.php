<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeAdvanceRequest;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeAdvanceController extends Controller
{
    public function index(Request $request): View
    {
        $advances = EmployeeAdvance::query()
            ->with('employee')
            ->orderByDesc('advance_date')
            ->paginate(30);

        return view('admin.advances.index', [
            'advances' => $advances,
        ]);
    }

    public function create(Request $request): View
    {
        $employees = Employee::query()->orderBy('full_name')->get();
        $selected = $request->input('employee_id');

        return view('admin.advances.create', [
            'employees' => $employees,
            'selected' => $selected,
        ]);
    }

    public function store(StoreEmployeeAdvanceRequest $request): RedirectResponse
    {
        EmployeeAdvance::create($request->validated());

        return redirect()
            ->route('admin.advances.index')
            ->with('status', 'Advance recorded.');
    }
}
