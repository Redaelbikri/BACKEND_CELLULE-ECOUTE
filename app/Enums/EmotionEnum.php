<?php

namespace App\Enums;

enum EmotionEnum: string
{
    case CALME = 'calme';
    case STRESS = 'stress';
    case ANXIETE = 'anxiete';
    case TRISTESSE = 'tristesse';
    case COLERE = 'colere';
    case CONFUSION = 'confusion';
    case FATIGUE = 'fatigue';
    case DEMOTIVATION = 'demotivation';
    case PEUR = 'peur';
    case SURCHARGE = 'surcharge';
    case ISOLEMENT = 'isolement';
    case BLOCAGE_ACADEMIQUE = 'blocage_academique';
    case INQUIETUDE = 'inquietude';
    case STABLE = 'stable';
    case URGENT = 'urgent';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
