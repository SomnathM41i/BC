<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'daily_salary',
        'gross_salary',
        'present_days',
        'half_days',
        'total_payable_days',
        'outstanding_advance_before',
        'advance_repayment',
        'net_salary_payable',
        'amount_paid',
        'remaining_salary_due',
        'remaining_advance_after',
        'status',
        'payment_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'daily_salary' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'present_days' => 'decimal:1',
            'half_days' => 'decimal:1',
            'total_payable_days' => 'decimal:1',
            'outstanding_advance_before' => 'decimal:2',
            'advance_repayment' => 'decimal:2',
            'net_salary_payable' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'remaining_salary_due' => 'decimal:2',
            'remaining_advance_after' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
