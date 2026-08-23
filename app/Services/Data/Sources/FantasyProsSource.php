<?php

namespace App\Services\Data\Sources;

use App\Facades\FantasyPros;

class FantasyProsSource extends BaseSource
{
    /* ===[ GETTERS ]=== */

    public function getNFLProjections(int $season, int $week)
    {
        return FantasyPros::projections()->getProjections($season, $week);
    }

    /* ===[ IMPORTERS ]=== */

    /**
     * Projections are imported by fantasy-pros:projections:import, which reads
     * what the fetch stage archived rather than pulling again.
     */
}
