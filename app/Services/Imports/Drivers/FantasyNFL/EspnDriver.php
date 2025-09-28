<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Models\League;
use App\Models\User;
use App\Services\Espn\Data\FantasyNFL\CredentialsData;

use Illuminate\Support\Collection;

class EspnDriver extends BaseFantasyNFLDriver
{
    public function setConfig(array|Collection $config)
    {
        $config = ($config instanceof Collection) ? $config : collect($config);

        $this->config->each(function ($val, $key) use ($config) {
            $this->config->set($key, $config->get($key, $val));
        });
    }

    public function importLeague(User $creator, array|CredentialsData $credentials): League
    {
        $importer = new EspnLeagueDriver(
            $creator,
            $credentials,
        );

        return $importer->import();
    }

    public function importRosters(League $league, int $year): League
    {
        $importer = new EspnRosterDriver($league, $year);

        return $importer->import();
    }
}
