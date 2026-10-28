<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeAttendanceRequest;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->input('date', now()->toDateString());
        $employees = Employee::query()->orderBy('full_name')->get();

        $existing = EmployeeAttendance::query()
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('employee_id');

        // Employees who had not joined yet on the selected date cannot be marked.
        $lockedEmployeeIds = $employees
            ->filter(fn (Employee $employee) => $employee->joining_date->gt($date))
            ->pluck('id')
            ->all();

        return view('admin.attendance.index', [
            'employees' => $employees,
            'date' => $date,
            'existing' => $existing,
            'lockedEmployeeIds' => $lockedEmployeeIds,
        ]);
    }

    public function weekly(Request $request): View
    {
        $employees = Employee::query()->orderBy('full_name')->get();
        $employeeId = $request->input('employee_id');
        $referenceDate = $request->input('reference_date', now()->toDateString());

        $employee = null;
        $dates = [];
        $existing = collect();
        $beforeJoining = false;

        if ($employeeId) {
            $employee = Employee::find($employeeId);
            if ($employee) {
                // Attendance is always edited inside the week that contains the
                // selected date, so today's day is never hidden in a past week.
                $period = $employee->getWeekContaining($referenceDate);
                $start = Carbon::parse($period['start_date']);
                $end = Carbon::parse($period['end_date']);
                $joining = $employee->joining_date->copy()->startOfDay();

                $beforeJoining = Carbon::parse($referenceDate)->lt($joining);

                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $dates[] = [
                        'date' => $date->toDateString(),
                        'label' => $date->format('D'),
                        'full' => $date->format('d-m-Y'),
                        'before_joining' => $date->lt($joining),
                        'is_today' => $date->isToday(),
                    ];
                }

                $existing = EmployeeAttendance::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('attendance_date', '>=', $start->toDateString())
                    ->whereDate('attendance_date', '<=', $end->toDateString())
                    ->get()
                    ->keyBy(fn ($att) => $att->attendance_date->toDateString());
            }
        }

        return view('admin.attendance.weekly', [
            'employees' => $employees,
            'employeeId' => $employeeId,
            'referenceDate' => $referenceDate,
            'employee' => $employee,
            'dates' => $dates,
            'existing' => $existing,
            'beforeJoining' => $beforeJoining,
        ]);
    }

    public function store(StoreEmployeeAttendanceRequest $request): RedirectResponse
    {
        // Weekly attendance save
        if ($request->has('weekly')) {
            $weekly = $request->input('weekly', []);

            DB::transaction(function () use ($weekly) {
                foreach ($weekly as $employeeId => $days) {
                    if (! is_array($days)) {
                        continue;
                    }

                    $employee = Employee::find($employeeId);
                    if (! $employee) {
                        continue;
                    }

                    foreach ($days as $date => $status) {
                        if (! in_array($status, ['present', 'absent', 'half_day'], true)) {
                            continue;
                        }

                        // Never record attendance before the joining date.
                        if (Carbon::parse($date)->lt($employee->joining_date->copy()->startOfDay())) {
                            continue;
                        }

                        $this->saveAttendance($employee->id, $date, $status);
                    }
                }
            });

            $employeeId = $request->input('weekly_employee_id');
            $ref = $request->input('weekly_reference_date');

            if ($employeeId && $ref) {
                return redirect()
                    ->route('admin.attendance.weekly', ['employee_id' => $employeeId, 'reference_date' => $ref])
                    ->with('status', 'Weekly attendance saved.');
            }

            return redirect()->route('admin.attendance.weekly')->with('status', 'Weekly attendance saved.');
        }

        // Single day attendance save
        $date = $request->validated('attendance_date');
        $attendance = $request->validated('attendance');

        DB::transaction(function () use ($date, $attendance) {
            $employees = Employee::query()
                ->whereIn('id', collect($attendance)->pluck('employee_id')->filter()->unique())
                ->get()
                ->keyBy('id');

            foreach ($attendance as $row) {
                $employeeId = $row['employee_id'] ?? null;
                $status = $row['status'] ?? null;

                if (! $employeeId || ! in_array($status, ['present', 'absent', 'half_day'], true)) {
                    continue;
                }

                $employee = $employees->get((int) $employeeId);
                if (! $employee) {
                    continue;
                }

                // Never record attendance before the joining date.
                if (Carbon::parse($date)->lt($employee->joining_date->copy()->startOfDay())) {
                    continue;
                }

                $this->saveAttendance($employee->id, $date, $status);
            }
        });

        return redirect()
            ->route('admin.attendance.index', ['date' => $date])
            ->with('status', 'Attendance saved.');
    }

    /**
     * Upsert one employee's attendance for a date. whereDate keeps the match
     * working on both MySQL (DATE column) and SQLite (datetime text).
     */
    private function saveAttendance(int $employeeId, string $date, string $status): void
    {
        $existing = EmployeeAttendance::query()
            ->where('employee_id', $employeeId)
            ->whereDate('attendance_date', $date)
            ->first();

        if ($existing) {
            $existing->update([
                'status' => $status,
                'payable_days' => EmployeeAttendance::payableDaysFor($status),
            ]);

            return;
        }

        EmployeeAttendance::create([
            'employee_id' => $employeeId,
            'attendance_date' => $date,
            'status' => $status,
            'payable_days' => EmployeeAttendance::payableDaysFor($status),
        ]);
    }
}
