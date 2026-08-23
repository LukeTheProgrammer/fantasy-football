<?php

namespace App\Enums;

enum NFLTeams: string
{
    case FA = 'FA';
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
    case GB = 'GB';
    case HOU = 'HOU';
    case IND = 'IND';
    case JAX = 'JAX';
    case KC = 'KC';
    case LV = 'LV';
    case LAR = 'LAR';
    case MIA = 'MIA';
    case MIN = 'MIN';
    case NE = 'NE';
    case NO = 'NO';
    case NYG = 'NYG';
    case NYJ = 'NYJ';
    case PHI = 'PHI';
    case PIT = 'PIT';
    case SEA = 'SEA';
    case SF = 'SF';
    case TB = 'TB';
    case TEN = 'TEN';
    case LAC = 'LAC';
    case WSH = 'WSH';

    /**
     * Abbreviations other sources use for teams we key differently.
     *
     * @var array<string, string>
     */
    public const ALIASES = [
        'ARZ' => 'ARI',
        'JAC' => 'JAX',
        'WAS' => 'WSH',
        'LA'  => 'LAR',
        'STL' => 'LAR',
        'SD'  => 'LAC',
        'OAK' => 'LV',
    ];

    /**
     * Resolve a team abbreviation from any source, aliases included.
     */
    public static function fromAbbreviation(?string $abbreviation): ?self
    {
        if (empty($abbreviation)) {
            return null;
        }

        $abbreviation = strtoupper(trim($abbreviation));

        return self::tryFrom(self::ALIASES[$abbreviation] ?? $abbreviation);
    }

    public static function options(): array
    {
        $opts = [];

        foreach (self::cases() as $case) {
            $opts[$case->value] = $case->value;
        }

        return $opts;
    }

    public static function values(): array
    {
        return array_map(
            fn ($case) => $case->value,
            self::cases()
        );
    }
}
