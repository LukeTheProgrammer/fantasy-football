<?php

namespace App\Enums;

enum Datum: string
{
    case SOURCE_ESPN         = 'ESPN';
    case SOURCE_FANTASY_PROS = 'FantasyPros';
    case SOURCE_PFR          = 'ProFootballReference';

    case FORMAT_RAW       = 'raw';
    case FORMAT_EXTRACTED = 'extracted';
    case FORMAT_FORMATTED = 'formatted';

    public const FORMATS = [
        self::FORMAT_RAW->value,
        self::FORMAT_EXTRACTED->value,
        self::FORMAT_FORMATTED->value,
    ];
}
