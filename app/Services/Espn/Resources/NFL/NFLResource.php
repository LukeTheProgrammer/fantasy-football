<?php

namespace App\Services\Espn\Resources\NFL;

use App\Services\Espn\Enums\Apis;
use App\Services\Espn\Enums\ApiVersions;
use App\Services\Espn\Enums\Leagues;
use App\Services\Espn\Enums\Sports;
use App\Services\Espn\Resources\BaseResource;

/**
 * News (Team Specific)	    site.api.espn.com/apis/site/v2/sports/football/nfl/news
 * Scoreboard (Site API)	site.api.espn.com/apis/site/v2/sports/football/nfl/scoreboard
 * Standings (Site API)	    site.api.espn.com/apis/site/v2/sports/football/nfl/standings
 * Game Summary (Site API)	site.api.espn.com/apis/site/v2/sports/football/nfl/summary
 * List Teams (Site API)	site.api.espn.com/apis/site/v2/sports/football/nfl/teams
 * Get Team (Site API)	    site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}
 * Team Roster (Site API)	site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/roster
 * Team Schedule (Site API)	site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/schedule
 * Leaders (Site API v3)	site.api.espn.com/apis/site/v3/sports/football/nfl/leaders
 */
abstract class NFLResource extends BaseResource
{
    public ?ApiVersions $apiVersion = ApiVersions::V2;

    public ?Apis $api = Apis::SITE;

    public ?Leagues $league = Leagues::NFL;

    public ?Sports $sport = Sports::FOOTBALL;

    public function __construct()
    {
        $this->cacheBaseDirectory = 'data/espn/nfl';
    }

    /**
     * Constructs URL string.
     *
     * @param string|null $path
     * @param string|null $version
     *
     * @return string
     */
    public function buildUrl(?string $path = null, ?string $version = null): string
    {
        $v = $version ?? $this->apiVersion->value;

        return $this->assembleUrl([
            'http://' . $this->api->value,
            'apis/site/' . $v,
            'sports/' . $this->sport->value,
            $this->league->value,
            $path,
        ]);
    }
}
