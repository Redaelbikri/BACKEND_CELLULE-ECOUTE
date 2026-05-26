<?php

namespace App\Enums;

enum AppointmentTypeEnum: string
{
    case PSYCHIQUE = 'psychique';
    case PHYSIQUE = 'physique';
    case ACADEMIQUE = 'academique';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
