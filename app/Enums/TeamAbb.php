<?php

namespace App\Enums;

enum TeamAbb: string
{
    case ARI = 'ARI';
    case ATL = 'ATL';
    case BAL = 'BAL';
    case BUF = 'BUF';
    case CAR = 'CAR';
    case CHI = 'CHI';
    case CIN = 'CIN';
    case CLE = 'CLE';
    case DAL = 'DAL';
    case DEN = 'DEN';
    case DET = 'DET';
    case GB  = 'GB';
    case HOU = 'HOU';
    case IND = 'IND';
    case JAX = 'JAX';
    case KC  = 'KC';
    case LV  = 'LV';
    case LAR = 'LAR';
    case MIA = 'MIA';
    case MIN = 'MIN';
    case NE  = 'NE';
    case NO  = 'NO';
    case NYG = 'NYG';
    case NYJ = 'NYJ';
    case PHI = 'PHI';
    case PIT = 'PIT';
    case SEA = 'SEA';
    case SF  = 'SF';
    case TB  = 'TB';
    case TEN = 'TEN';
    case LAC = 'LAC';
    case WSH = 'WSH';

    public static function options(): array
    {
        $opts = [];

        foreach (static::cases() as $case) {
            $opts[$case->value] = $case;
        }

        return $opts;
    }
}
