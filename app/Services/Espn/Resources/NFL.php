<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\Resources\NFL\GetEventSummary;
use App\Services\Espn\Resources\NFL\GetLeaders;
use App\Services\Espn\Resources\NFL\GetRoster;
use App\Services\Espn\Resources\NFL\GetScoreboard;
use App\Services\Espn\Resources\NFL\GetTeam;
use App\Services\Espn\Resources\NFL\GetTeamNews;
use App\Services\Espn\Resources\NFL\GetTeamSchedule;

class NFL extends BaseResourceCollection
{
    /**
     * News
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/news
     *
     * @return mixed
     */
    public function getTeamNews(int|string|null $teamId = null)
    {
        $resource = new GetTeamNews($teamId);

        $resource->teamId = $teamId;

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
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
        $resource = new GetScoreboard();

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
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
        $resource = new GetEventSummary();

        $resource->eventId = $eventId;

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }

    /**
     * List Teams
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/teams
     *
     * @return mixed
     */
    public function getTeam(int|string|null $teamId = null)
    {
        $resource = new GetTeam();

        $resource->teamId = $teamId;

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }

    /**
     * Roster
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/roster
     *
     * @return mixed
     */
    public function getRoster(int|string $teamId)
    {
        $resource = new GetRoster();

        $resource->teamId = $teamId;

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }

    /**
     * Team Schedule
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/schedule
     *
     * @return mixed
     */
    public function getTeamSchedule(int|string $teamId, int $year)
    {
        $resource = new GetTeamSchedule();

        $resource->teamId = $teamId;
        $resource->year = $year;

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
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
        $resource = new GetLeaders();

        if ($this->forcePull) {
            $resource->forcePull();
        }

        return $resource->fetch();
    }

}
