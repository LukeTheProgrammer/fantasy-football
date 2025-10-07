<?php

namespace App\Services\Espn\Resources\FantasyNFL;

use App\Services\Espn\Data\FantasyNFL\ResourceLeagueData;
use App\Services\Espn\Enums\FantasyNFLViews;
use Illuminate\Http\Client\Response;

class GetSettings extends FantasyNFLResource
{
    public function setCacheFilePath()
    {
        $dirs = [
            'league-' . $this->leagueId,
            $this->dataFormat,
        ];

        $file = [
            'settings',
            date('Y-m-d'),
        ];

        // EX: data/espn/ffl/leagues/settings-123456-2025-10-03-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function setOpts(int|string $teamId)
    {
        $this->teamId = $teamId;
    }

    public function sendRequest()
    {
        $views = [
            FantasyNFLViews::SETTINGS,
            FantasyNFLViews::TEAM,
            FantasyNFLViews::MODULAR,
            FantasyNFLViews::NAV,
        ];

        $url = $this->buildUrl($views, $this->teamId);

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
