<?php

namespace App\Services\Data\Sources;

use App\Enums\NFLTeams;
use App\Models\League;
use App\Models\Team;

/**
 * Data Source classes should have a method for each of the commands
 * in the app/Console/Commands/Data directory.
 *
 * Not all sources will have all methods implemented.
 */
abstract class BaseSource
{
    /**
     * Get methods are to pull data from external sources and save
     * the data somewhere within the app for later use without
     * needing to re-pull the data.
     */

    abstract public function getFantasyDraftRankings();

    abstract public function getFantasyLeague(?League $league = null, ?array $credentials = null);

    abstract public function getFantasyLeagueRosters(League $league, int $year);

    abstract public function getNFLProjections();

    abstract public function getNFLRosters(Team|NFLTeams|string $team);

    abstract public function getNFLSchedule(Team|NFLTeams|string $team, int $year);

    /**
     * Import methods are to import data pulled by the get methods.
     */

    abstract public function importFantasyDraftRankings();

    abstract public function importFantasyLeague(array $leagueData = []);

    abstract public function importFantasyLeagueRosters(League $league, int $year);

    abstract public function importNFLProjections();

    abstract public function importNFLRosters();

    abstract public function importNFLSchedule();

    abstract public function importPositions();

    abstract public function importTeams();
}
