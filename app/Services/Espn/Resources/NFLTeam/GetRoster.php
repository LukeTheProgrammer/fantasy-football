<?php

namespace App\Services\Espn\Resources\NFLTeam;

use App\Models\Team;
use App\Services\Espn\Scrapers\NFLRosters;
use Exception;

class GetRoster extends NFLTeamResource
{
    public function setCacheFilePath()
    {
        $dirs = ['rosters'];

        $file = [
            'team',
            $this->teamId,
            $this->dataFormat,
        ];

        // EX: data/espn/nfl-teams/roster-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $scraper = new NFLRosters();

        $team = Team::forEspnId($this->teamId)->first();

        if (! $team instanceof Team) {
            throw new Exception('Team not found for espn id: ' . $this->teamId);
        }

        $data = $scraper->getTeamRoster($team);

        return $this->returnResponse($data);
    }
}
