<?php

namespace App\Services\ProFootballReference;

use App\Enums\NFLTeams;
use App\Models\Team;
use App\Services\ProFootballReference\Resources\RostersResource;

class ProFootballReferenceService
{
    public function getRoster(Team|NFLTeams $team, int $year): array
    {
        return (new RostersResource())->getTeamRoster($team, $year);
    }
}
