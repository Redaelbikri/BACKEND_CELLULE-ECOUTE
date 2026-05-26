<?php

namespace App\Enums;

enum ProblemTypeEnum: string
{
    case PSYCHIQUE = 'psychique';
    case PHYSIQUE = 'physique';
    case ACADEMIQUE = 'academique';
    case SOCIAL = 'social';
    case ORIENTATION = 'orientation';
    case ORGANISATION = 'organisation';
    case AUTRE = 'autre';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
