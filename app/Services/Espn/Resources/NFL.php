<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\Enums\Apis;
use App\Services\Espn\Enums\ApiVersions;
use App\Services\Espn\Enums\Leagues;
use App\Services\Espn\Enums\Sports;

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
class NFL extends BaseResource
{
    public ?ApiVersions $apiVersion = ApiVersions::V2;
    public ?Apis $api = Apis::SITE;
    public ?Leagues $league = Leagues::NFL;
    public ?Sports $sport = Sports::FOOTBALL;

    public function __construct()
    {
        //
    }

    public function buildUrl(?string $path = null, ?string $version = null)
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

    /**
     * News
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/news
     *
     * @return mixed
     */
    public function getTeamNews(int|string|null $teamId = null)
    {
        $url = $this->buildUrl('news');

        $response = $this->get($url, $this->query([
            'team' => $teamId,
        ]));

        return $response->json();
    }

    /**
     * Scoreboard
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/scoreboard
     *
     * @return mixed
     */
    public function getScoreboard()
    {
        $url = $this->buildUrl('scoreboard');

        $response = $this->get($url, $this->query());

        return $response->json();
    }

    /**
     * Event Summary
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/summary
     *
     * @return mixed
     */
    public function getEventSummary(int|string $eventId)
    {
        $url = $this->buildUrl('summary');

        $response = $this->get($url, $this->query([
            'event' => $eventId,
        ]));

        return $response->json();
    }

    /**
     * List Teams
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/teams
     *
     * @return mixed
     */
    public function getTeams()
    {
        $url = $this->buildUrl('teams');

        $response = $this->get($url, $this->query());

        return $response->json();
    }

    /**
     * Get Team
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}
     *
     * @return mixed
     */
    public function getTeam(int $teamId)
    {
        $url = $this->buildUrl('teams/' . $teamId);

        $response = $this->get($url, $this->query());

        return $response->json();
    }

    /**
     * Team Roster
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/roster
     *
     * @return mixed
     */
    public function getTeamRoster(int $teamId)
    {
        $url = $this->buildUrl('teams/' . $teamId . '/roster');

        $response = $this->get($url, $this->query());

        return $response->json();
    }

    /**
     * Team Schedule
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/schedule
     *
     * @return mixed
     */
    public function getTeamSchedule(int $teamId)
    {
        $url = $this->buildUrl('teams/' . $teamId . '/schedule');

        $response = $this->get($url, $this->query());

        return $response->json();
    }

    /**
     * Leaders
     *
     * site.api.espn.com/apis/site/v3/sports/football/nfl/leaders
     *
     * @return mixed
     */
    public function getLeaders()
    {
        $url = $this->buildUrl('leaders', ApiVersions::V3->value);

        $response = $this->get($url, $this->query());

        return $response->json();
    }
}
