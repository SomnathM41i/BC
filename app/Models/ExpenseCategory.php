<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    public const CONTEXT_PROJECT = 'project';

    public const CONTEXT_OWNER = 'owner';

    public const CONTEXT_OWNER_INCOME = 'owner_income';

    protected $fillable = [
        'name',
        'context',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProject($query)
    {
        return $query->where('context', self::CONTEXT_PROJECT);
    }

    public function scopeForOwner($query)
    {
        return $query->where('context', self::CONTEXT_OWNER);
    }

    public function scopeForOwnerIncome($query)
    {
        return $query->where('context', self::CONTEXT_OWNER_INCOME);
    }
}
