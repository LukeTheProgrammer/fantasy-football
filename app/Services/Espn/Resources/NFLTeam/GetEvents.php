<?php

namespace App\Services\Espn\Resources\NFLTeam;

class GetEvents extends NFLTeamResource
{
    public function setCacheFilePath()
    {
        $dirs = ['nfl-teams'];

        $file = [
            'events',
            $this->teamId,
            $this->dataFormat,
        ];

        // EX: data/espn/nfl-teams/events-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('events');

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
