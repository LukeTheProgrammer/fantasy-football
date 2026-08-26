<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Models\League;
use Illuminate\Support\Collection;

abstract class BaseFantasyNFLDriver
{
    protected Collection $config;

    public function getConfig(?string $key = null): mixed
    {
        return $key ? $this->config->get($key) : $this->config;
    }

    abstract public function setConfig(array|Collection $config);

    abstract public function importLeague(array $leagueData = []): League;

    abstract public function importRosters(League $league, int $season): League;
}
