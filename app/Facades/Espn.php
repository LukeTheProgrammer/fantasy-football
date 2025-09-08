<?php

namespace App\Facades;

use App\Services\Espn\Resources\NFL;
use App\Services\Espn\Resources\NflTeam;
use App\Services\Espn\Resources\NflFantasyLeague;
use Illuminate\Support\Facades\Facade;

/**
 * @method static NFL nfl()
 * @method static NflTeam team(int|string $id)
 * @method static NflFantasyLeague fantasyLeague(int|string $leagueId)
 */
class Espn extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Espn';
    }
}
