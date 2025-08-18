<?php

namespace App\Services\Espn;

use App\Services\Espn\Resources\Players;
use App\Services\Espn\Resources\Teams;

class EspnService
{
    public function getTeam(int|string $id)
    {
        return (new Teams())->getTeam($id);
    }

    public function getTeamPlayers(int|string $id, int $pageIndex = 1)
    {
        return (new Players())->getTeamPlayers($id, $pageIndex);
    }

    public function getPlayer(int|string $id)
    {
        return (new Players())->getPlayer($id);
    }
}
