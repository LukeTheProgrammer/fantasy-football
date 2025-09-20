<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\BaseData;

class PlayerOwnershipData extends BaseData
{
    public function __construct(
        public ?float $auctionValueAverage = null,
        public ?float $averageDraftPosition = null,
        public ?float $percentChange = null,
        public ?float $percentOwned = null,
        public ?float $percentStarted = null,
    ) {
        //
    }
}
