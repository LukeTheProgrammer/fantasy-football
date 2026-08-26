<?php

namespace App\Services\Imports\Drivers\NFLStats;

use App\Facades\Nflverse;
use Generator;

class NflverseDriver extends BaseNFLStatsDriver
{
    public function __construct(private bool $forcePull = false)
    {
        //
    }

    public function players(): Generator
    {
        return Nflverse::forcePull($this->forcePull)->players();
    }

    public function games(?int $season = null): Generator
    {
        return Nflverse::forcePull($this->forcePull)->games($season);
    }

    public function stats(int $season, string $window): Generator
    {
        return Nflverse::forcePull($this->forcePull)->playerStats($season, $window);
    }
}
