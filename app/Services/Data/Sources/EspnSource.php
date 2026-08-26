<?php

namespace App\Services\Data\Sources;

use App\Enums\FantasyPlatforms;
use App\Facades\Espn;
use App\Facades\Import;
use App\Models\League;
use App\Models\Team;
use App\Services\Espn\Data\FantasyNFL\CredentialsData;
use App\Services\Espn\EspnConstants;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class EspnSource extends BaseSource
{
    /* ===[ GETTERS ]=== */

    public function getFantasyLeague(?League $league = null, array|CredentialsData|null $credentials = null, int|string|null $season = null)
    {
        if ($league === null && $credentials === null) {
            throw new InvalidArgumentException('League or credentials must be provided');
        }

        return Espn::dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getFantasyLeague(
                ($league instanceof League) ? $league->credentials : $credentials,
                $season
            );
    }

    public function getFantasyLeagueRosters(League $league, int $season)
    {
        $rosters = [];

        $league->members->each(function ($member) use (&$rosters, $league, $season) {
            $memberRosters = [];

            for ($week = 1; $week <= 18; $week++) {
                $weekKey = 'week.' . $week;
                $data = Espn::dataFormat($this->dataFormat)
                    ->forcePull($this->forcePull)
                    ->getFantasyRoster($league->credentials, [
                        'teamId' => $member->external_id,
                        'week'   => $week,
                        'season' => $season,
                    ]);

                $memberRosters[$weekKey] = ($data instanceof Collection) ? $data->first() : $data[0];
            }

            $memberKey = 'member.' . $member->id;
            $rosters[$memberKey] = collect($memberRosters);
        });

        return collect($rosters);
    }

    public function getNFLRosters(Team $team, int $season)
    {
        return Espn::dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getNFLTeamRoster($team, $season);
    }

    public function getNFLSchedule(Team $team, int $season)
    {
        return Espn::dataFormat($this->dataFormat)
            ->forcePull($this->forcePull)
            ->getNFLSchedule($team, $season);
    }

    /* ===[ IMPORTERS ]=== */

    public function importFantasyLeague(array $leagueData = [])
    {
        if (empty($leagueData)) {
            throw new InvalidArgumentException('League data must be provided');
        }

        $importer = Import::fantasyNFL(FantasyPlatforms::ESPN->value);

        return $importer->importLeague($leagueData);
    }

    public function importFantasyRosters(League $league, int $season)
    {
        $importer = Import::fantasyNFL(FantasyPlatforms::ESPN->value);

        return $importer->importRosters($league, $season);
    }

    public function importNFLRosters(Team $team, int $season)
    {
        $importer = Import::nfl(FantasyPlatforms::ESPN->value);

        return $importer->importRosters($team, $season);
    }

    public function importNFLSchedule(Team $team, int $season)
    {
        $importer = Import::nfl(FantasyPlatforms::ESPN->value);

        return $importer->importSchedule($team, $season);
    }

    /* ===[ FORMATTERS ]=== */

    public function sortFantasyLineup(League $league, array|Collection $roster)
    {
        return Espn::sortFantasyLineup($league, $roster);
    }

    public function lineupSlotName(mixed $lineupSlotId)
    {
        $name = Arr::get(EspnConstants::POSITION_SLOT_MAP, $lineupSlotId);

        return ($name === EspnConstants::POSITION_SLOT_MAP[23]) ? 'Flex' : $name;
    }
}
