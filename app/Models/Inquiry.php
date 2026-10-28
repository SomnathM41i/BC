<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_READ = 'read';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'name',
        'phone',
        'location',
        'budget',
        'email',
        'service',
        'message',
        'locale',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }
}
