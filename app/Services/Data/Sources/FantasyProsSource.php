<?php

namespace App\Services\Data\Sources;

use App\Enums\NFLTeams;
use App\Facades\Espn;
use App\Models\League;
use App\Models\Team;
use InvalidArgumentException;

class FantasyProsSource extends BaseSource
{
    /* ===[ GETTERS ]=== */

    public function getFantasyDraftRankings()
    {
        return null;
    }

    public function getFantasyLeague(?League $league = null, ?array $credentials = null)
    {
        if (null === $league && null === $credentials) {
            throw new InvalidArgumentException('League or credentials must be provided');
        }

        $espn = Espn::fantasyNFL(
            ($league instanceof League) ? $league->credentials : $credentials
        );

        $espn->getLeague();

        return true;
    }

    public function getFantasyLeagueRosters(League $league, int $year)
    {
        $espn = Espn::fantasyNFL($league->credentials);

        $league->members->each(function ($member) use ($espn, $year) {
            for ($week = 1; $week <= 18; $week++) {
                $espn->getRostersForTeam($member->external_id, $week, $year);
            }
        });

        return true;
    }

    public function getNFLProjections()
    {
        return null;
    }

    public function getNFLRosters(Team|NFLTeams|string $team)
    {
        Espn::nflTeam()->getRoster($team);

        return true;
    }

    public function getNFLSchedule(Team|NFLTeams|string $team, int $year)
    {
        Espn::nfl()->getTeamSchedule($team, $year);

        return true;
    }

    /* ===[ IMPORTERS ]=== */

    public function importFantasyDraftRankings()
    {
        return null;
    }

    public function importFantasyLeague()
    {
        return null;
    }

    public function importFantasyLeagueRosters()
    {
        return null;
    }

    public function importNFLProjections()
    {
        return null;
    }

    public function importNFLRosters()
    {
        return null;
    }

    public function importNFLSchedule()
    {
        return null;
    }

    public function importPositions()
    {
        return null;
    }

    public function importTeams()
    {
        return null;
    }
}
