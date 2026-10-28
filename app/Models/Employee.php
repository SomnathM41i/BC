<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'full_name',
        'phone_number',
        'daily_salary',
        'weekly_salary_payment_day',
        'joining_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'daily_salary' => 'decimal:2',
            'joining_date' => 'date',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function salaryPayments(): HasMany
    {
        return $this->hasMany(EmployeeSalaryPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function totalAdvances(): string
    {
        $total = $this->advances()->sum('amount');

        return Money::normalize($total ?? '0');
    }

    /**
     * Repayments are recorded once, on the salary payment that deducted them.
     * Summing that column keeps partial repayments exact and prevents any
     * advance row from being counted twice.
     */
    public function totalRepayments(): string
    {
        $total = $this->salaryPayments()->sum('advance_repayment');

        return Money::normalize($total ?? '0');
    }

    public function outstandingAdvance(): string
    {
        return Money::sub($this->totalAdvances(), $this->totalRepayments());
    }

    public static function generateEmployeeId(): string
    {
        $year = now()->format('Y');
        $last = static::query()
            ->where('employee_id', 'like', "EMP-{$year}-%")
            ->orderBy('employee_id', 'desc')
            ->first();

        $number = 1;
        if ($last && preg_match('/EMP-\d{4}-(\d+)/', $last->employee_id, $m)) {
            $number = ((int) $m[1]) + 1;
        }

        return 'EMP-'.$year.'-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * The last completed salary week: the 7 days ending on the most recent
     * payment day on or before the reference date. Used for paying salary.
     */
    public function getWeeklyPeriod(Carbon|string $referenceDate): array
    {
        $date = $referenceDate instanceof Carbon ? $referenceDate->copy() : Carbon::parse($referenceDate);
        $target = $this->paymentDayOfWeek();

        $end = $date->copy();
        if ((int) $end->dayOfWeek !== $target) {
            $end = $end->previous($target);
        }

        return $this->periodAround($end);
    }

    /**
     * The salary week that contains the reference date: the 7 days ending on
     * the next payment day on or after it. Used for marking attendance, so
     * today is always inside the week being edited.
     */
    public function getWeekContaining(Carbon|string $referenceDate): array
    {
        $date = $referenceDate instanceof Carbon ? $referenceDate->copy() : Carbon::parse($referenceDate);
        $target = $this->paymentDayOfWeek();

        $end = $date->copy();
        if ((int) $end->dayOfWeek !== $target) {
            $end = $end->next($target);
        }

        return $this->periodAround($end);
    }

    public function paymentDayOfWeek(): int
    {
        return [
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
        ][strtolower($this->weekly_salary_payment_day)] ?? Carbon::SATURDAY;
    }

    /**
     * @return array{start: Carbon, end: Carbon, start_date: string, end_date: string}
     */
    protected function periodAround(Carbon $end): array
    {
        $start = $end->copy()->subDays(6);

        return [
            'start' => $start->copy()->startOfDay(),
            'end' => $end->copy()->endOfDay(),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }
}
