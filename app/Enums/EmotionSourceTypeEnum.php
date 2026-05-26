<?php

namespace App\Enums;

enum EmotionSourceTypeEnum: string
{
    case APPOINTMENT_REASON = 'appointment_reason';
    case CHAT_MESSAGE = 'chat_message';
    case DOCUMENT = 'document';
    case MANUAL = 'manual';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
