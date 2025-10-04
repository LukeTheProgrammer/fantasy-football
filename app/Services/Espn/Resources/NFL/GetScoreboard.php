<?php

namespace App\Services\Espn\Resources\NFL;

class GetScoreboard extends NFLResource
{
    public function setCacheFilePath()
    {
        $dirs = ['scoreboards'];

        $file = [
            date('Y-m-d'),
            $this->dataFormat,
        ];

        // EX: data/espn/nfl/scoreboards/2025-10-03-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl('scoreboard');

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
