<?php

namespace App\Services\Imports\Drivers\NFL;

use App\Facades\Espn;
use App\Enums\NFLTeams;
use App\Models\Team;

class EspnNFLDriver extends BaseNFLDriver
{
    public function importRosters(Team|NFLTeams|string $team, int $year)
    {
        $roster = Espn::nfl()->getTeamRoster($team);
    }
}
