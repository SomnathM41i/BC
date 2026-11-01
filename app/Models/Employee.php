<?php

namespace App\Models;

use App\Support\Money;
use App\Support\WorkLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'full_name',
        'phone_number',
        'employee_type_id',
        'daily_salary',
        'half_day_amount',
        'long_day_amount',
        'weekly_salary_payment_day',
        'joining_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'daily_salary' => 'decimal:2',
            'half_day_amount' => 'decimal:2',
            'long_day_amount' => 'decimal:2',
            'joining_date' => 'date',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class, 'employee_type_id');
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

    /**
     * What each work level pays this employee today.
     *
     * @return array<string, string>
     */
    public function dayAmounts(): array
    {
        $amounts = self::calculatedAmounts($this->daily_salary ?? '0');

        $amounts['half_day'] = $this->half_day_amount ?? $amounts['half_day'];
        $amounts['long_day'] = $this->long_day_amount ?? $amounts['long_day'];

        return $amounts;
    }

    public function hasCustomDayAmounts(): bool
    {
        return $this->half_day_amount !== null || $this->long_day_amount !== null;
    }

    /**
     * Save the Pay Details amounts. Half and long day stay calculated unless
     * the owner actually changed them, so a raised salary still carries through.
     *
     * @param  array{normal_day: string, half_day?: ?string, long_day?: ?string}  $amounts
     */
    public function saveDayAmounts(array $amounts): void
    {
        $before = $this->dayAmounts();
        $salary = Money::normalize($amounts['normal_day']);
        $calculated = self::calculatedAmounts($salary);
        $values = ['daily_salary' => $salary];

        foreach (['half_day' => 'half_day_amount', 'long_day' => 'long_day_amount'] as $level => $column) {
            $amount = $amounts[$level] ?? null;
            $unchanged = $amount !== null && $this->{$column} === null && Money::compare($amount, $before[$level]) === 0;

            $values[$column] = $amount === null || $unchanged || Money::compare($amount, $calculated[$level]) === 0
                ? null
                : Money::normalize($amount);
        }

        $this->update($values);
    }

    /**
     * @return array<string, string>
     */
    private static function calculatedAmounts(string $salary): array
    {
        return array_map(
            fn (string $multiplier) => bcmul($salary, $multiplier, Money::SCALE),
            WorkLevel::MULTIPLIERS
        );
    }

    /**
     * Whether a salary payment already covers this date. Paid weeks are locked
     * so attendance can never drift away from what was actually paid.
     */
    public function isDatePaid(string $date): bool
    {
        return $this->salaryPayments()
            ->whereDate('period_start', '<=', $date)
            ->whereDate('period_end', '>=', $date)
            ->exists();
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
