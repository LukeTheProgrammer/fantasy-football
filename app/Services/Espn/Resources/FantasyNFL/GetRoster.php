<?php

namespace App\Services\Espn\Resources\FantasyNFL;

use App\Services\Espn\Enums\FantasyNFLViews;
use App\Services\Espn\Extractors\FantasyRosterExtractor;
use App\Services\Espn\Formatters\FantasyRosterFormatter;
use Illuminate\Http\Client\Response;

class GetRoster extends FantasyNFLResource
{
    public int|string $week;

    public int|string $year;

    public function setCacheFilePath()
    {
        $dirs = [
            'league-' . $this->leagueId,
            $this->dataFormat,
        ];

        $file = [
            'roster',
            $this->teamId,
            $this->week,
            $this->year,
        ];

        // EX: data/espn/ffl/leagues/league-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function setOpts(int|string $teamId, int|string $week, int|string $year)
    {
        $this->teamId = $teamId;
        $this->week = $week;
        $this->year = $year;
    }

    public function sendRequest()
    {
        $views = [
            FantasyNFLViews::ROSTER,
        ];

        $url = $this->buildUrl($views, null, $this->year);

        $url .= '&forTeamId=' . $this->teamId;
        $url .= '&scoringPeriodId=' . $this->week;

        $response = $this->get($url, null, $this->cookies);

        return $this->returnResponse($response);
    }

    public function returnExtracted(array|Response $response)
    {
        return FantasyRosterExtractor::from(
            (is_array($response)) ? $response : $response->json()
        );
    }

    public function returnFormatted(array|Response $response)
    {
        return FantasyRosterFormatter::from(
            (is_array($response)) ? $response : $response->json(),
            $this->year,
            $this->week
        );
    }
}
