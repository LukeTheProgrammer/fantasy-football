<?php

namespace App\Services\Data\Sources;

use App\Models\League;
use App\Models\Team;
use App\Traits\HasDataFormats;
use Illuminate\Support\Collection;

/**
 * Data Source classes should have a method for each of the commands
 * in the app/Console/Commands/Data directory.
 *
 * Not all sources will have all methods implemented.
 */
abstract class BaseSource
{
    use HasDataFormats;

    /**
     * Get methods are to pull data from external sources and save
     * the data somewhere within the app for later use without
     * needing to re-pull the data.
     */
    public function getFantasyDraftRankings()
    {
        return null;
    }

    public function getFantasyLeague(?League $league = null, ?array $credentials = null)
    {
        return null;
    }

    public function getFantasyRosters(League $league, int $season)
    {
        return null;
    }

    public function getNFLProjections(int $season, int $week)
    {
        return null;
    }

    public function getNFLRosters(Team $team, int $season)
    {
        return null;
    }

    public function getNFLSchedule(Team $team, int $season)
    {
        return null;
    }

    /**
     * Import methods are to import data pulled by the get methods.
     */
    public function importFantasyDraftRankings()
    {
        return null;
    }

    public function importFantasyLeague(array $leagueData = [])
    {
        return null;
    }

    public function importFantasyRosters(League $league, int $season)
    {
        return null;
    }

    public function importNFLProjections(int $season, int $week)
    {
        return null;
    }

    public function importNFLRosters(Team $team, int $season)
    {
        return null;
    }

    public function importNFLSchedule(Team $team, int $season)
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

    /**
     * Data formatting helper methods.
     */
    public function sortFantasyLineup(League $league, array|Collection $roster)
    {
        return $roster;
    }

    public function lineupSlotName(mixed $lineupSlotId)
    {
        return null;
    }
}
