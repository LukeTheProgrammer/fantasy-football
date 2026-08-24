<?php

namespace App\Services\CBS\Resources\FantasyNFL;

use App\Services\CBS\Enums\FantasyNFLViews;
use App\Services\CBS\Extractors\FantasyRosterExtractor;
use App\Services\CBS\Formatters\FantasyRosterFormatter;
use Illuminate\Http\Client\Response;

class GetRoster extends FantasyNFLResource
{
    public int|string $week;

    public int|string $season;

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
            $this->season,
        ];

        // EX: data/espn/ffl/leagues/league-123456-formatted.json
        $this->cacheFilePath = $this->getCacheFilePath($dirs, $file);
    }

    public function setOpts(int|string $teamId, int|string $week, int|string $season)
    {
        $this->teamId = $teamId;
        $this->week = $week;
        $this->season = $season;
    }

    public function sendRequest()
    {
        $views = [
            FantasyNFLViews::ROSTER,
        ];

        $url = $this->buildUrl($views, null, $this->season);

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
            $this->season,
            $this->week
        );
    }
}
