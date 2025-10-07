<?php

namespace App\Services\Espn\Resources;

use App\Models\Team;
use App\Services\Espn\Resources\NFL\GetEventSummary;
use App\Services\Espn\Resources\NFL\GetLeaders;
use App\Services\Espn\Resources\NFL\GetRoster;
use App\Services\Espn\Resources\NFL\GetSchedule;
use App\Services\Espn\Resources\NFL\GetScoreboard;
use App\Services\Espn\Resources\NFL\GetTeam;
use App\Services\Espn\Resources\NFL\GetTeamNews;

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

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->teamId = $teamId;

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

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

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

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->eventId = $eventId;

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

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->teamId = $teamId;

        return $resource->fetch();
    }

    /**
     * Roster
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/roster
     *
     * @return mixed
     */
    public function getRoster(int|string|null $teamId = null)
    {
        $resource = new GetRoster();

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->teamId = $teamId;

        return $resource->fetch();
    }

    /**
     * Team Schedule
     *
     * site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/schedule
     *
     * @return mixed
     */
    public function getSchedule(Team $team, int $year)
    {
        $resource = new GetSchedule();

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        $resource->team = $team;
        $resource->year = $year;

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

        $resource->forcePull($this->forcePull);
        $resource->dataFormat($this->dataFormat);

        return $resource->fetch();
    }

}
