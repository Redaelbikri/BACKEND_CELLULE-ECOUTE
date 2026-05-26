<?php

namespace App\Enums;

enum SentimentEnum: string
{
    case POSITIVE = 'positive';
    case NEUTRAL = 'neutral';
    case NEGATIVE = 'negative';
    case MIXED = 'mixed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
