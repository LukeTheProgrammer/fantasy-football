<?php

namespace App\Services\Espn\Resources\NFLTeam;

use App\Enums\NFLTeams;
use App\Models\Team;
use App\Services\Espn\Enums\Apis;
use App\Services\Espn\Enums\ApiVersions;
use App\Services\Espn\Enums\ApiYears;
use App\Services\Espn\Enums\Games;
use App\Services\Espn\Enums\Leagues;
use App\Services\Espn\Enums\Sports;
use App\Services\Espn\Resources\BaseResource;

abstract class NFLTeamResource extends BaseResource
{
    public ?ApiVersions $apiVersion = ApiVersions::V2;

    public ?ApiYears $apiYear = ApiYears::Y_2026;

    public ?Apis $api = Apis::SPORTS_CORE;

    public ?Games $game = Games::FANTASY_FOOTBALL;

    public ?Leagues $league = Leagues::NFL;

    public ?Sports $sport = Sports::FOOTBALL;

    public function __construct(Team|NFLTeams|int|string $team)
    {
        $this->setTeamId($team);

        $this->cacheBaseDirectory = 'data/espn/nfl-teams';
    }

    public function buildUrl(?string $path = null, array $params = [])
    {
        $scheme = $params['scheme'] ?? 'http';
        $version = $params['version'] ?? $this->apiVersion->value;
        $season = $params['season'] ?? $this->apiYear->value;

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
}
