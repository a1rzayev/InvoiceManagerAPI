<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SELLER = 'seller';
    case CLIENT = 'client';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this)
        {
            self::ADMIN => 'Administrator',
            self::SELLER => 'Shop',
            self::CLIENT => 'Client'
        };
    }
}
