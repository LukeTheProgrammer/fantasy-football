<?php

namespace App\Services\Imports\Drivers\NFL;

use App\Models\Team;

abstract class BaseNFLDriver
{
    public function importRosters(Team $team, int $season)
    {
        //
    }

    public function importSchedule(Team $team, int $season)
    {
        //
    }
}
