<?php

namespace App\Services\Espn;

use App\Models\League;
use App\Models\Team;
use App\Services\Espn\Data\FantasyNFL\CredentialsData;
use App\Services\Espn\Formatters\FantasyLineupFormatter;
use App\Services\Espn\Resources\FantasyNFL;
use App\Services\Espn\Resources\NFL;
use App\Services\Espn\Resources\NflTeam;
use App\Traits\HasDataFormats;
use Illuminate\Support\Collection;

/**
 * @see https://github.com/pseudo-r/Public-ESPN-API
 * @see https://gist.github.com/nntrn/ee26cb2a0716de0947a0a4e9a157bc1c/2fa98612cedcbad033d4206b16cd360c9b654ae9
 */
class EspnService
{
    use HasDataFormats;


    /* ===[ NFL ]=== */


    public function getNFLTeamNews(Team $team)
    {
        $resource = new NFL();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getTeamNews($team);
    }

    public function getNFLScoreboard()
    {
        $resource = new NFL();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getScoreboard();
    }

    public function getNFLEventSummary(int|string $eventId)
    {
        $resource = new NFL();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getEventSummary($eventId);
    }

    public function getNFLTeam(?Team $team = null)
    {
        $resource = new NFL();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getTeam($team);
    }

    public function getNFLRoster(?Team $team = null)
    {
        $resource = new NFL();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getRoster($team);
    }

    public function getNFLSchedule(Team $team, int $season)
    {
        $resource = new NFL();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getSchedule($team, $season);
    }

    public function getNFLLeaders()
    {
        $resource = new NFL();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getLeaders();
    }


    /* ===[ NFL Team ]=== */


    public function getNFLTeamDepthChart(Team $team)
    {
        $resource = new NFLTeam();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getDepthChart($team);
    }

    public function getNFLTeamEvents(Team $team)
    {
        $resource = new NFLTeam();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getEvents($team);
    }

    public function getNFLPlayers(Team $team, int $page = 1)
    {
        $resource = new NFLTeam();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getPlayers($team, $page);
    }

    public function getNFLTeamRoster(Team $team, int|string|null $season = null)
    {
        $resource = new NFLTeam();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getRoster($team, $season);
    }

    public function getNFLTeamData(Team $team)
    {
        $resource = new NFLTeam();

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getTeam($team);
    }


    /* ===[ Fantasy NFL ]=== */


    public function getFantasyDraft(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new FantasyNFL($credentials);

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getDraftRecap($credentials, $opts);
    }

    public function getFantasyLeague(array|CredentialsData $credentials, int|string|null $season = null)
    {
        $resource = new FantasyNFL($credentials);

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getLeague($credentials, $season);
    }

    public function getFantasyMatchup(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new FantasyNFL($credentials);

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getMatchup($credentials, $opts);
    }

    public function getFantasyRoster(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new FantasyNFL($credentials);

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getRoster($credentials, $opts);
    }

    public function getFantasySettings(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new FantasyNFL($credentials);

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getSettings($credentials, $opts);
    }

    public function getFantasyStandings(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new FantasyNFL($credentials);

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getStandings($credentials, $opts);
    }

    public function getFantasyTeams(array|CredentialsData $credentials, array $opts = [])
    {
        $resource = new FantasyNFL($credentials);

        return $resource->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getTeams($credentials, $opts);
    }

    public function sortFantasyLineup(League $league, array|Collection $roster)
    {
        return FantasyLineupFormatter::from($league, $roster);
    }
}
