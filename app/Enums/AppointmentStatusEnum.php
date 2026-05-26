<?php
namespace App\Enums;
enum AppointmentStatusEnum: string
   {
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
