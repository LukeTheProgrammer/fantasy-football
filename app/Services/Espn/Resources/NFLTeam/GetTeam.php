<?php

namespace App\Services\Espn\Resources\NFLTeam;

class GetTeam extends NFLTeamResource
{
    public function setCacheFilePath()
    {
        $dirs = [];

        $file = [
            'team',
            $this->teamId,
            $this->dataFormat,
        ];

        // EX: data/espn/nfl-teams/team-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl();

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
