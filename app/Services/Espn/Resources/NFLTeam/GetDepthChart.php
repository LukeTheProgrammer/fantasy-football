<?php

namespace App\Services\Espn\Resources\NFLTeam;

class GetDepthChart extends NFLTeamResource
{
    public function setCacheFilePath()
    {
        $dirs = [];

        $file = [
            'depthchart',
            $this->teamId,
            $this->returnType,
        ];

        // EX: data/espn/nfl-teams/depthchart-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('depthcharts');

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
