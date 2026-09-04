<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Models\League;
use Illuminate\Support\Collection;
use RuntimeException;

class CbsDriver extends BaseFantasyNFLDriver
{
    public function __construct()
    {
        $this->config = collect();
    }

    public function setConfig(array|Collection $config)
    {
        $this->config = ($config instanceof Collection) ? $config : collect($config);
    }

    public function importLeague(array $leagueData = []): League
    {
        $importer = new CbsLeagueDriver($leagueData);

        return $importer->import();
    }

    /**
     * CBS serves a roster per team per week, which the draft assistant has no
     * use for until the season is under way.
     */
    public function importRosters(League $league, int $season): League
    {
        throw new RuntimeException('CBS roster import is not implemented.');
    }
}
