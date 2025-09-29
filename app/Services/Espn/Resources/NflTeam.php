<?php

namespace App\Services\Espn\Resources;

use App\Enums\NFLTeams;
use App\Services\Espn\Enums\Apis;
use App\Services\Espn\Enums\ApiVersions;
use App\Services\Espn\Enums\ApiYears;
use App\Services\Espn\Enums\Games;
use App\Services\Espn\Enums\Leagues;
use App\Services\Espn\Enums\Sports;
use App\Services\Espn\Scrapers\NFLRosters;

class NflTeam extends BaseResource
{
    public ?ApiVersions $apiVersion = ApiVersions::V2;
    public ?ApiYears $apiYear = ApiYears::Y_2025;
    public ?Apis $api = Apis::SPORTS_CORE;
    public ?Games $game = Games::FANTASY_FOOTBALL;
    public ?Leagues $league = Leagues::NFL;
    public ?Sports $sport = Sports::FOOTBALL;

    public function __construct(public int|string $teamId)
    {
        //
    }

    public function buildUrl(?string $path = null, array $params = [])
    {
        $scheme = $params['scheme'] ?? 'http';
        $version = $params['version'] ?? $this->apiVersion->value;
        $season = $params['year'] ?? $this->apiYear->value;

        // http://sports.core.api.espn.com/v2/sports/football/leagues/nfl/seasons/2025/teams
        return $this->assembleUrl([
            $scheme . '://' . $this->api->value,
            $version,
            'sports/' . $this->sport->value,
            'leagues/' . $this->league->value,
            'seasons/' . $season,
            'teams/' . $this->teamId,
            $path,
        ]);
    }

    public function getTeam()
    {
        // /teams/1
        $url = $this->buildUrl();

        $response = $this->get($url, $this->query());

        return $response->json();
    }

    public function getPlayers(int $page = 1)
    {
        // /teams/1/athletes
        $url = $this->buildUrl('athletes');

        $response = $this->get($url, $this->query(['page' => $page]));

        return $response->json();
    }

    public function getDepthChart()
    {
        // /teams/1/depthcharts
        $url = $this->buildUrl('depthcharts');

        $response = $this->get($url, $this->query());

        return $response->json();
    }

    public function getEvents()
    {
        // /teams/1/events
        $url = $this->buildUrl('events');

        $response = $this->get($url, $this->query());

        return $response->json();
    }

    public function getRoster(string|NFLTeams $team)
    {
        $scraper = new NFLRosters();

        return $scraper->getTeamRoster($team);
    }
}
