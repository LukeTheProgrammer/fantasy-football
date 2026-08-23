<?php

namespace App\Facades;

use App\Services\FantasyPros\Resources\ProjectionsResource;
use App\Services\FantasyPros\Resources\RankingsResource;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ProjectionsResource projections()
 * @method static RankingsResource rankings()
 */
class FantasyPros extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'FantasyPros';
    }
}
