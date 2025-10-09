<?php

namespace App\Services\Espn\Resources\NFL;

use App\Models\Team;

class GetTeam extends NFLResource
{
    public Team|null $team = null;

    public function setCacheFilePath()
    {
        $dirs = ['teams'];

        $file = [
            (null !== $this->team->espn_id) ? 'team' : 'teams',
            (null !== $this->team->espn_id) ? $this->team->espn_id : null,
            $this->dataFormat,
        ];

        // EX: data/espn/nfl/teams/teams-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('teams');

        if (null !== $this->team->espn_id) {
            $url .= '/' . $this->team->espn_id;
        }

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
