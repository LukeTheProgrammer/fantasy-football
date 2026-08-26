<?php

namespace App\Services\Espn\Resources\NFL;

use App\Models\Team;

class GetTeam extends NFLResource
{
    public ?Team $team = null;

    public function setCacheFilePath()
    {
        $dirs = ['teams'];

        $file = [
            ($this->team->espn_id !== null) ? 'team' : 'teams',
            ($this->team->espn_id !== null) ? $this->team->espn_id : null,
            $this->dataFormat,
        ];

        // EX: data/espn/nfl/teams/teams-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('teams');

        if ($this->team->espn_id !== null) {
            $url .= '/' . $this->team->espn_id;
        }

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
