<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner / Admin',
        };
    }
}
