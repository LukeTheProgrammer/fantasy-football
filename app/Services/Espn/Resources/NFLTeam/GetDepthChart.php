<?php

namespace App\Services\Espn\Resources\NFLTeam;

class GetDepthChart extends NFLTeamResource
{
    public function setCacheFilePath()
    {
        $dirs = ['nfl-teams'];

        $file = [
            'depthchart',
            $this->teamId,
            date('Y-m-d'),
            $this->dataFormat,
        ];

        // EX: data/espn/nfl-teams/depthchart-123456-2025-10-03-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('depthcharts');

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
