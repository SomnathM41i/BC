<?php

namespace App\Support;

use App\Models\EmployeeAttendance;

/**
 * The fixed work levels. A day pays the employee's normal day salary times the
 * level's multiplier, which is also how much of a day it counts as.
 */
class WorkLevel
{
    /** In display order. */
    public const LABELS = [
        'half_day' => 'Half day',
        'normal_day' => 'Normal day',
        'long_day' => 'Long day',
    ];

    public const MULTIPLIERS = [
        'half_day' => '0.5',
        'normal_day' => '1.0',
        'long_day' => '1.5',
    ];

    /**
     * One line per level and amount, so a salary change inside a week stays
     * visible. Days marked before work levels existed have a null level.
     *
     * @param  iterable<EmployeeAttendance>  $attendances
     * @return list<array{level: ?string, days: int, amount: string}>
     */
    public static function breakdownFor(iterable $attendances): array
    {
        $lines = [];

        foreach ($attendances as $attendance) {
            if ($attendance->status !== 'present') {
                continue;
            }

            $key = $attendance->work_level.'|'.$attendance->amount;
            $lines[$key] ??= ['level' => $attendance->work_level, 'days' => 0, 'amount' => $attendance->amount];
            $lines[$key]['days']++;
        }

        $order = array_flip(array_keys(self::LABELS));

        usort($lines, fn (array $a, array $b) => [$order[$a['level']] ?? -1, (float) $a['amount']] <=> [$order[$b['level']] ?? -1, (float) $b['amount']]);

        return $lines;
    }

    /**
     * @param  list<array{level: ?string, days: int, amount: string}>|null  $breakdown
     */
    public static function describe(?array $breakdown, ?callable $label = null): string
    {
        $label ??= fn (?string $level) => self::LABELS[$level] ?? 'Present';

        return collect($breakdown ?? [])
            ->map(fn (array $line) => $label($line['level']).': '.$line['days'].' × '.Money::inr($line['amount']))
            ->implode(', ');
    }
}
