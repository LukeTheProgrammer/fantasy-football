<?php

namespace App\Services\Data\Sources;

use App\Enums\Datum;
use App\Facades\Import;
use App\Facades\FantasyPros;

class FantasyProsSource extends BaseSource
{

    /* ===[ GETTERS ]=== */


    public function getNFLProjections(int $season, int $week)
    {
        return FantasyPros::projections()->getAllProjections($season, $week);
    }


    /* ===[ IMPORTERS ]=== */


    public function importNFLProjections(int $season, int $week)
    {
        $import = Import::projections(Datum::SOURCE_FANTASY_PROS->value);

        $import->setUp(['season' => $season,'week' => $week]);

        $import->load();

        return $import->getErrors();
    }
}
