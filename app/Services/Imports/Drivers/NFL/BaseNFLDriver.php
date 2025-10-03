<?php

namespace App\Services\Imports\Drivers\NFL;

use App\Enums\NFLTeams;
use App\Models\Team;

abstract class BaseNFLDriver
{
    public function importRosters(Team|NFLTeams|string $team, int $year)
    {
        //
    }
}
