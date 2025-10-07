<?php

namespace App\Services\Data\Sources;

use App\Enums\Datum;
use App\Facades\Import;
use App\Facades\FantasyPros;

class FantasyProsSource extends BaseSource
{

    /* ===[ GETTERS ]=== */


    public function getNFLProjections(int $year, int $week)
    {
        return FantasyPros::projections()->getAllProjections($year, $week);
    }


    /* ===[ IMPORTERS ]=== */


    public function importNFLProjections(int $year, int $week)
    {
        $import = Import::projections(Datum::SOURCE_FANTASY_PROS->value);

        $import->setUp(['year' => $year,'week' => $week]);

        $import->load();

        return $import->getErrors();
    }
}
