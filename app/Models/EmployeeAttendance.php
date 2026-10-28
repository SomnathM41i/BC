<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'status',
        'payable_days',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'payable_days' => 'decimal:1',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public static function payableDaysFor(string $status): string
    {
        return match ($status) {
            'present' => '1.0',
            'half_day' => '0.5',
            'absent' => '0.0',
            default => '0.0',
        };
    }
}
