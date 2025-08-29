<?php

namespace App\Facades;

use App\Services\Imports\Models\DraftRankingsImport;
use Illuminate\Support\Facades\Facade;

/**
 * @method static DraftRankingsImport draftRankingsImport(string $driver, ...$args): DraftRankingsImport
 */
class Import extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Import';
    }
}
