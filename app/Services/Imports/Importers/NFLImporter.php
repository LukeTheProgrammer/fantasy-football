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

    public function importRosters(Team $team, int $year)
    {
        return $this->driver->importRosters($team, $year);
    }

    public function importSchedule(Team $team, int $year)
    {
        return $this->driver->importSchedule($team, $year);
    }
}
