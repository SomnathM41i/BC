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

            if (blank($contractor->statement_token)) {
                $contractor->statement_token = static::generateStatementToken();
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

    /**
     * A long random token used in the public statement URL.
     *
     * The token replaces the guessable slug so only the owner's shared link
     * opens a contractor's payment statement.
     */
    public static function generateStatementToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::query()->where('statement_token', $token)->exists());

        return $token;
    }

    /**
     * The statement token, generated lazily for rows created before tokens.
     */
    public function statementToken(): string
    {
        if (blank($this->statement_token)) {
            $this->forceFill(['statement_token' => static::generateStatementToken()])->saveQuietly();
        }

        return (string) $this->statement_token;
    }

    public function statementUrl(): string
    {
        return route('statements.show', ['contractor' => $this->statementToken()]);
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
