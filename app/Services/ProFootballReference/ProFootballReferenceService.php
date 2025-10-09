<?php

namespace App\Services\ProFootballReference;

use App\Models\Team;
use App\Services\ProFootballReference\Resources\RostersResource;

class ProFootballReferenceService
{
    public function getRoster(Team $team, int $season): array
    {
        return (new RostersResource())->getTeamRoster($team, $season);
    }
}
