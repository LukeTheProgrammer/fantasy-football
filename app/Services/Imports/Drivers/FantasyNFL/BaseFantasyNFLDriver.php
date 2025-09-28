<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Models\League;
use App\Models\User;
use App\Services\Espn\Data\FantasyNFL\CredentialsData;
use Illuminate\Support\Collection;

abstract class BaseFantasyNFLDriver
{
    protected Collection $config;

    public function getConfig(?string $key = null): mixed
    {
        return $key ? $this->config->get($key) : $this->config;
    }

    abstract public function setConfig(array|Collection $config);
    abstract public function importLeague(User $creator, array|CredentialsData $credentials): League;
    abstract public function importRosters(League $league, int $year): League;
}
