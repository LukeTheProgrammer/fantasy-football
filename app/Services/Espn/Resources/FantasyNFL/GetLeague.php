<?php

namespace App\Services\Espn\Resources\FantasyNFL;

use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use Illuminate\Http\Client\Response;

class GetLeague extends FantasyNFLResource
{
    public function setCacheFilePath()
    {
        $dirs = ['leagues'];

        $file = [
            'league',
            $this->leagueId,
            $this->returnType,
        ];

        // EX: data/espn/ffl/leagues/league-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl([]);

        $response = $this->get($url, null, $this->cookies);

        return $this->returnResponse($response);
    }

    public function returnExtracted(array|Response $response)
    {
        return ResourceLeagueData::from(
            (is_array($response)) ? $response : $response->json()
        );
    }

    public function returnFormatted(array|Response $response)
    {
        return ResourceLeagueData::from(
            (is_array($response)) ? $response : $response->json()
        );
    }
}
