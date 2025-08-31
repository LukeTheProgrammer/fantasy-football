<?php

namespace App\Services\Espn;

use App\Services\Espn\Resources\Players;
use App\Services\Espn\Resources\Rosters;
use App\Services\Espn\Resources\Teams;

/**
 * @see https://gist.github.com/nntrn/ee26cb2a0716de0947a0a4e9a157bc1c/2fa98612cedcbad033d4206b16cd360c9b654ae9
 */
class EspnService
{
    public function getTeam(int|string $id)
    {
        return (new Teams)->getTeam($id);
    }

    public function getTeamPlayers(int|string $id, int $pageIndex = 1)
    {
        return (new Teams)->getPlayers($id, $pageIndex);
    }

    public function getPlayer(int|string $id)
    {
        return (new Players)->getPlayer($id);
    }

    public function getRoster(int|string $id)
    {
        return (new Rosters)->getRoster($id);
    }
}
