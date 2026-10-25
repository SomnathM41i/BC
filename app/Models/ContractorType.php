<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractorType extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Contractor>
     */
    public function contractors(): HasMany
    {
        return $this->hasMany(Contractor::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
