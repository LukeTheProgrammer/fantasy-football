<?php

namespace App\Services\Data\Sources;

use App\Enums\FantasyPlatforms;
use App\Enums\NFLTeams;
use App\Facades\Espn;
use App\Facades\Import;
use App\Models\League;
use App\Models\Team;
use App\Services\Espn\Data\FantasyNFL\CredentialsData;
use InvalidArgumentException;

class EspnSource extends BaseSource
{
    /* ===[ GETTERS ]=== */

    public function getFantasyDraftRankings()
    {
        return null;
    }

    public function getFantasyLeague(?League $league = null, array|CredentialsData|null $credentials = null)
    {
        if (null === $league && null === $credentials) {
            throw new InvalidArgumentException('League or credentials must be provided');
        }

        return Espn::dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getFantasyLeague(
                ($league instanceof League) ? $league->credentials : $credentials
            );
    }

    public function getFantasyLeagueRosters(League $league, int $year)
    {
        $rosters = [];

        $league->members->each(function ($member) use (&$rosters,$league, $year) {
            $memberRosters = [];

            for ($week = 1; $week <= 18; $week++) {
                $weekKey = 'week.' . $week;
                $data = Espn::dataFormat($this->dataFormat)
                    ->forcePull($this->forcePull)
                    ->getFantasyRoster($league->credentials, [
                        'teamId' => $member->external_id,
                        'week' => $week,
                        'year' => $year,
                    ]);

                $memberRosters[$weekKey] = $data[0];
            }

            $memberKey = 'member.' . $member->id;
            $rosters[$memberKey] = collect($memberRosters);
        });

        return collect($rosters);
    }

    public function getNFLProjections()
    {
        return null;
    }

    public function getNFLRosters(Team|NFLTeams|string $team)
    {
        return Espn::dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getNFLTeamRoster($team);
    }

    public function getNFLSchedule(Team|NFLTeams|string $team, int $year)
    {
        return Espn::dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getTeamSchedule($team, $year);
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

    public function importFantasyRosters(League $league, int $year)
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
