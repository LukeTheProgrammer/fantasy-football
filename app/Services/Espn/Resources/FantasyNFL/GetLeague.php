<?php

namespace App\Services\Espn\Resources\FantasyNFL;

use App\Services\Espn\Extractors\FantasyLeagueExtractor;
use App\Services\Espn\Formatters\FantasyLeagueFormatter;
use Illuminate\Http\Client\Response;

class GetLeague extends FantasyNFLResource
{
    public int|string|null $season = null;

    public function setCacheFilePath()
    {
        $dirs = [
            'league-' . $this->leagueId,
            $this->dataFormat,
        ];

        $file = [
            'league',
            $this->season ?? $this->apiYear->value,
            date('Y-m-d'),
        ];

        // EX: data/espn/ffl/leagues/league-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function sendRequest()
    {
        $url = $this->buildUrl([], null, $this->season ? (int) $this->season : null);

        $response = $this->get($url, null, $this->cookies);

        return $this->returnResponse($response);
    }

    public function returnExtracted(array|Response $response)
    {
        return FantasyLeagueExtractor::from($response);
    }

    public function returnFormatted(array|Response $response)
    {
        return FantasyLeagueFormatter::from(
            $this->returnExtracted($response)
        );
    }
}
