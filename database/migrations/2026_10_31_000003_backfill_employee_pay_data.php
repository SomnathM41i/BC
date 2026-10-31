<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Moves existing employees onto the new pay model without changing what
     * anyone earns: each keeps custom amounts based on their daily salary, and
     * marked days get the amount the old formula would have paid. Salary
     * payments are history and stay untouched.
     */
    public function up(): void
    {
        $otherTypeId = DB::table('employee_types')->where('name', 'Other')->value('id');
        $hours = DB::table('employee_pay_rates')
            ->where('employee_type_id', $otherTypeId)
            ->pluck('hours_label', 'work_level');

        $now = now();

        DB::table('employees')->whereNull('employee_type_id')->orderBy('id')->get()
            ->each(function ($employee) use ($otherTypeId, $now) {
                DB::table('employees')->where('id', $employee->id)->update(['employee_type_id' => $otherTypeId]);

                if (bccomp((string) $employee->daily_salary, '0', 2) <= 0) {
                    return;
                }

                foreach (['half_day' => '0.5', 'normal_day' => '1.0', 'long_day' => '1.5'] as $level => $multiplier) {
                    DB::table('employee_pay_rate_overrides')->insertOrIgnore([
                        'employee_id' => $employee->id,
                        'work_level' => $level,
                        'amount' => bcmul((string) $employee->daily_salary, $multiplier, 2),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });

        $salaries = DB::table('employees')->pluck('daily_salary', 'id');

        DB::table('employee_attendances')
            ->whereNull('work_level')
            ->where('status', '!=', 'absent')
            ->orderBy('id')
            ->get()
            ->each(function ($attendance) use ($salaries, $hours) {
                $level = $attendance->status === 'half_day' ? 'half_day' : 'normal_day';

                DB::table('employee_attendances')->where('id', $attendance->id)->update([
                    'work_level' => $level,
                    'hours_label' => $hours[$level] ?? null,
                    'amount' => bcmul((string) ($salaries[$attendance->employee_id] ?? '0'), (string) $attendance->payable_days, 2),
                ]);
            });
    }

    public function down(): void
    {
        // The schema rollback removes everything this migration wrote.
    }
};
