<?php

namespace App\Services\Imports\Drivers\NFLStats;

use Generator;

/**
 * Pulls player, game and stat rows already shaped for the app's tables.
 *
 * A driver knows where the rows come from and nothing about how they are
 * stored, so a second source only has to produce the same shape.
 */
abstract class BaseNFLStatsDriver
{
    /**
     * @return Generator<int, array<string, mixed>>
     */
    abstract public function players(): Generator;

    /**
     * @return Generator<int, array<string, mixed>>
     */
    abstract public function games(?int $season = null): Generator;

    /**
     * @return Generator<int, array<string, mixed>>
     */
    abstract public function stats(int $season, string $window): Generator;
}
