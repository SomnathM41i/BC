<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Contractor extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'phone',
        'contractor_type_id',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Contractor $contractor): void {
            if (blank($contractor->slug)) {
                $contractor->slug = static::generateSlug($contractor->name);
            }
        });
    }

    /**
     * Build a unique, URL-safe slug from a contractor name.
     */
    public static function generateSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'contractor';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ContractorType::class, 'contractor_type_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Projects this contractor is primarily assigned to.
     *
     * @return HasMany<Project>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @return HasMany<WalletTransaction>
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'contractor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
