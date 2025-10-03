<?php

namespace App\Services\Espn\Resources\NFL;

class GetTeam extends NFLResource
{
    public int|string|null $teamId = null;

    public function setCacheFilePath()
    {
        $dirs = ['teams'];

        $file = [
            (null !== $this->teamId) ? 'team' : 'teams',
            (null !== $this->teamId) ? $this->teamId : null,
            $this->returnType,
        ];

        // EX: data/espn/nfl/teams/teams-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('teams');

        if (null !== $this->teamId) {
            $url .= '/' . $this->teamId;
        }

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
