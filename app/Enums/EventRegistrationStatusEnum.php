<?php

namespace App\Enums;

enum EventRegistrationStatusEnum: string
{
    case REGISTERED = 'registered';
    case CANCELLED = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
