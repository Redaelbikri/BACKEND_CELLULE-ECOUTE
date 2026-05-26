<?php

namespace App\Enums;

enum EventTypeEnum: string
{
    case PSYCHIQUE = 'psychique';
    case PHYSIQUE = 'physique';
    case ACADEMIQUE = 'academique';
    case SENSIBILISATION = 'sensibilisation';
    case ORIENTATION = 'orientation';
    case RELAXATION = 'relaxation';
    case MOTIVATION = 'motivation';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
