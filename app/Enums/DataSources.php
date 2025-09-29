<?php

namespace App\Enums;

enum DataSources: string
{
    case ESPN         = 'ESPN';
    case FANTASY_PROS = 'FantasyPros';
    case PFR          = 'ProFootballReference';
}
