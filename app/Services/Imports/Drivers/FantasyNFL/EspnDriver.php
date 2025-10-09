<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Models\League;
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

    public function importLeague(array $leagueData = []): League
    {
        $importer = new EspnLeagueDriver($leagueData);

        return $importer->import();
    }

    public function importRosters(League $league, int $season): League
    {
        $importer = new EspnRosterDriver($league, $season);

        return $importer->import();
    }
}
