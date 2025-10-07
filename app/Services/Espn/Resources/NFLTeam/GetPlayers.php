<?php

namespace App\Services\Espn\Resources\NFLTeam;

class GetPlayers extends NFLTeamResource
{
    public int $page = 1;

    public function setCacheFilePath()
    {
        $dirs = ['players'];

        $file = [
            'team',
            $this->teamId,
            $this->page,
            $this->dataFormat,
        ];

        // EX: data/espn/nfl-teams/players-123456-1-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('athletes');

        $response = $this->get($url, $this->query(['page' => $this->page]));

        return $this->returnResponse($response);
    }
}
