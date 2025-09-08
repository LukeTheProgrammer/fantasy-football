<?php

namespace App\Facades;

use App\Services\Espn\Resources\FantasyLeagues;
use App\Services\Espn\Resources\Players;
use App\Services\Espn\Resources\Rosters;
use App\Services\Espn\Resources\Teams;
use Illuminate\Support\Facades\Facade;

/**
 * @method static FantasyLeagues fantasyLeague(int|string $leagueId)
 * @method static Players player(int|string $id)
 * @method static Rosters roster(int|string $id)
 * @method static Teams team(int|string $id)
 * @method static Teams teamPlayers(int|string $id, int $pageIndex = 1)
 */
class Espn extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Espn';
    }
}
