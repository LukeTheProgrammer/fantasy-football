<?php

namespace App\Services\Data\Sources;

use App\Enums\FantasyPlatforms;
use App\Enums\NFLTeams;
use App\Facades\Espn;
use App\Facades\Import;
use App\Models\League;
use App\Models\Team;
use InvalidArgumentException;

class EspnSource extends BaseSource
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

        Espn::formatted()->getFantasyLeague(
            ($league instanceof League) ? $league->credentials : $credentials
        );

        return true;
    }

    public function getFantasyLeagueRosters(League $league, int $year)
    {
        $league->members->each(function ($member) use ($league, $year) {
            for ($week = 1; $week <= 18; $week++) {
                Espn::formatted()->getFantasyLeagueRoster($league->credentials, [
                    'teamId' => $member->external_id,
                    'week' => $week,
                    'year' => $year,
                ]);
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

    public function importFantasyLeague(array $leagueData = [])
    {
        if (empty($leagueData)) {
            throw new InvalidArgumentException('League data must be provided');
        }

        $importer = Import::fantasyNFL(FantasyPlatforms::ESPN);

        $importer->importLeague($leagueData);
    }

    public function importFantasyLeagueRosters(League $league, int $year)
    {
        $importer = Import::fantasyNFL(FantasyPlatforms::ESPN);

        $importer->importRosters($league, $year);
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
