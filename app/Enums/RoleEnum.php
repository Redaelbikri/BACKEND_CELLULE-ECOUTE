<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case STUDENT = 'student';
    case COUNSELOR = 'counselor';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
