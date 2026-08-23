<?php

namespace App\Services\CBS\Resources;

class OwnersResource extends BaseResource
{
    public function setCacheFilePath()
    {
        $dirs = [
            'league-' . $this->leagueId,
            $this->dataFormat,
        ];

        $file = [
            'league',
            date('Y-m-d'),
        ];

        // EX: data/espn/ffl/leagues/league-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        // $url = $this->assembleUrl([]);
        $url = 'http://api.cbssports.com/fantasy/league/owners?version=3.0&response_format=json';

        $response = $this->get($url);

        return $this->returnResponse($response);
    }
}
