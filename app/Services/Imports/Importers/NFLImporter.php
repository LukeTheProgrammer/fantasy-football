<?php

namespace App\Services\Imports\Importers;

use App\Models\Team;
use App\Services\Imports\Drivers\NFL\BaseNFLDriver;

class NFLImporter
{
    public function __construct(public ?BaseNFLDriver $driver = null)
    {
        //
    }

    public function importRosters(Team $team, int $season)
    {
        return $this->driver->importRosters($team, $season);
    }

    public function importSchedule(Team $team, int $season)
    {
        return $this->driver->importSchedule($team, $season);
    }
}
