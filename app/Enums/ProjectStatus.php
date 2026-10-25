<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case ClosurePending = 'closure_pending';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::ClosurePending => 'Pending Closure',
            self::Closed => 'Closed',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isClosed(): bool
    {
        return $this === self::Closed;
    }
}
