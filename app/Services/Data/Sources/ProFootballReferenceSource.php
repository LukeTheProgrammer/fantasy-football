<?php

namespace App\Services\Data\Sources;

use App\Enums\Datum;
use App\Facades\Import;
use App\Facades\ProFootballReference;
use App\Models\Team;

class ProFootballReferenceSource extends BaseSource
{
    /* ===[ GETTERS ]=== */

    public function getNFLRosters(Team $team, int $season)
    {
        return ProFootballReference::getRoster($team, $season);
    }

    /* ===[ IMPORTERS ]=== */

    public function importNFLRosters(Team $team, int $season)
    {
        return Import::nfl(Datum::SOURCE_PFR->value)->importRosters($team, $season);
    }
}
