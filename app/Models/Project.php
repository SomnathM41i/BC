<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\TransactionType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'client_name',
        'client_mobile',
        'client_mobile_normalized',
        'agreed_amount',
        'contractor_id',
        'status',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'agreed_amount' => 'decimal:2',
            'status' => ProjectStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)
            ->where('type', TransactionType::Receipt->value);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)
            ->where('type', TransactionType::ProjectExpense->value);
    }

    public function contractorTransfers(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)
            ->where('type', TransactionType::ContractorTransfer->value);
    }

    public function closureReleases(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)
            ->where('type', TransactionType::ClosureRelease->value);
    }

    public function isActive(): bool
    {
        return $this->status === ProjectStatus::Active;
    }

    public function isClosurePending(): bool
    {
        return $this->status === ProjectStatus::ClosurePending;
    }

    public function isClosed(): bool
    {
        return $this->status === ProjectStatus::Closed;
    }

    /**
     * Total money actually received against this project (from receipt history).
     */
    public function totalReceived(): string
    {
        $receipts = $this->relationLoaded('receipts')
            ? $this->receipts
            : $this->receipts()->get();

        return Money::sum($receipts, fn (WalletTransaction $t) => (string) $t->amount);
    }

    /**
     * Remaining amount still to be received against the agreed project amount.
     */
    public function remainingAgainstAgreed(): string
    {
        return Money::sub((string) $this->agreed_amount, $this->totalReceived());
    }

    public function scopeActive($query)
    {
        return $query->where('status', ProjectStatus::Active->value);
    }

    public function scopeNotClosed($query)
    {
        return $query->where('status', '!=', ProjectStatus::Closed->value);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', ProjectStatus::Closed->value);
    }
}
