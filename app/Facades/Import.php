<?php

namespace App\Facades;

use App\Services\Imports\Importers\FantasyProsProjectionsImporter;
use App\Services\Imports\Importers\FantasyProsRankingsImporter;
use Illuminate\Support\Facades\Facade;

/**
 * @method static FantasyProsProjectionsImporter fantasyProsProjections()
 * @method static FantasyProsRankingsImporter fantasyProsRankings()
 */
class Import extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Import';
    }
}
