<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum FantasyPlatformsEnum: string
{
    case CBS  = 'CBS';
    case ESPN = 'ESPN';

    public static function options(): Collection
    {
        return collect(self::cases())->pluck('value', 'value');
    }
}
