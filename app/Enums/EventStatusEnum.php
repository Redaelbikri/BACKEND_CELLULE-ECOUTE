<?php

namespace App\Enums;

enum EventStatusEnum: string
{
    case UPCOMING = 'upcoming';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
