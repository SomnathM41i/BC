<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeAttendanceRequest;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $employees = Employee::query()->orderBy('full_name')->get();
        $employeeId = $request->input('employee_id');
        $referenceDate = $request->input('reference_date', now()->toDateString());

        $employee = null;
        $dates = [];
        $existing = collect();

        if ($employeeId) {
            $employee = Employee::find($employeeId);
            if ($employee) {
                // Attendance is always edited inside the week that contains the
                // selected date, so today's day is never hidden in a past week.
                $period = $employee->getWeekContaining($referenceDate);
                $start = Carbon::parse($period['start_date']);
                $end = Carbon::parse($period['end_date']);
                $joining = $employee->joining_date->copy()->startOfDay();

                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $dates[] = [
                        'date' => $date->toDateString(),
                        'label' => $date->format('D'),
                        'full' => $date->format('d-m-Y'),
                        'before_joining' => $date->lt($joining),
                        'paid' => $employee->isDatePaid($date->toDateString()),
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

        return view('admin.attendance.index', [
            'employees' => $employees,
            'employeeId' => $employeeId,
            'referenceDate' => $referenceDate,
            'employee' => $employee,
            'dates' => $dates,
            'existing' => $existing,
        ]);
    }

    public function store(StoreEmployeeAttendanceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $problems = [];

        DB::transaction(function () use ($data, &$problems) {
            // Lock the employee so a salary payment cannot be recorded for this
            // week while its attendance is being changed.
            $employee = Employee::query()->whereKey($data['employee_id'])->lockForUpdate()->firstOrFail();

            foreach ($data['days'] as $date => $day) {
                $status = $day['status'] ?? null;

                // Never record attendance before the joining date.
                if (! $status || Carbon::parse($date)->lt($employee->joining_date->copy()->startOfDay())) {
                    continue;
                }

                $problems[] = $this->saveDay($employee, $date, $status, $day['amount'] ?? null);
            }
        });

        $redirect = redirect()->route('admin.attendance.index', [
            'employee_id' => $data['employee_id'],
            'reference_date' => $data['reference_date'],
        ]);
        $problems = array_filter($problems);

        return $problems === []
            ? $redirect->with('status', 'Attendance saved.')
            : $redirect->with('error', 'Some days were not saved. '.implode(' ', $problems));
    }

    /**
     * Upsert one day and return why it could not be saved, if so. whereDate
     * keeps the match working on both MySQL (DATE column) and SQLite (datetime
     * text).
     */
    private function saveDay(Employee $employee, string $date, string $status, ?string $amount): ?string
    {
        $day = Carbon::parse($date)->format('d-m-Y');

        if ($employee->isDatePaid($date)) {
            return "{$day}: this salary week is already paid.";
        }

        $amount = Money::normalize($amount ?? '0');

        if ($status === 'present' && Money::isZero($amount)) {
            return "{$day}: enter the amount for a present day.";
        }

        $values = [
            'status' => $status,
            'payable_days' => $status === 'present' ? '1.0' : '0.0',
            'amount' => $status === 'present' ? $amount : '0.00',
        ];

        $existing = EmployeeAttendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $date)
            ->first();

        if ($existing) {
            $existing->update($values);

            return null;
        }

        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => $date,
            ...$values,
        ]);

        return null;
    }
}
