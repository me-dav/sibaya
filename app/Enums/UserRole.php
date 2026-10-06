<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SISWA = 'siswa';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::SISWA => 'Siswa',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
