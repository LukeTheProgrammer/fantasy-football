<?php

namespace App\Services\Rankings;

use App\Models\League;
use App\Services\Rankings\Actions\ResolveFormatAction;

/**
 * Which published rankings a league should be read against.
 */
class RankingService
{
    /**
     * @return array{0: float, 1: bool}
     */
    public function format(League $league): array
    {
        return (new ResolveFormatAction)->run($league);
    }
}
