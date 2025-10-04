<?php

namespace App\Services\Espn\Resources\NFLTeam;

use App\Models\Team;
use App\Services\Espn\Scrapers\NFLRosters;
use Exception;
use Illuminate\Support\Arr;

class GetRoster extends NFLTeamResource
{
    public function setCacheFilePath()
    {
        $dirs = ['nfl-teams'];

        $file = [
            'roster',
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

        return $this->returnResponse(Arr::get($data, 'roster', []));
    }
}
