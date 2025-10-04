<?php

namespace App\Services\Espn\Resources;

use App\Enums\NFLTeams;
use App\Models\Team;
use App\Services\Espn\Resources\NFLTeam\GetDepthChart;
use App\Services\Espn\Resources\NFLTeam\GetEvents;
use App\Services\Espn\Resources\NFLTeam\GetPlayers;
use App\Services\Espn\Resources\NFLTeam\GetRoster;
use App\Services\Espn\Resources\NFLTeam\GetTeam;

class NflTeam extends BaseResourceCollection
{
    public function getDepthChart(Team|NFLTeams|int|string $team)
    {
        $resource = new GetDepthChart($team);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }

    public function getEvents(Team|NFLTeams|int|string $team)
    {
        $resource = new GetEvents($team);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }

    public function getPlayers(Team|NFLTeams|int|string $team, int $page = 1)
    {
        $resource = new GetPlayers($team);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->page = $page;

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }

    public function getRoster(Team|NFLTeams|int|string $team)
    {
        $resource = new GetRoster($team);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }

    public function getTeam(Team|NFLTeams|int|string $team)
    {
        $resource = new GetTeam($team);

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }
}
