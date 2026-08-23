<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;

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
