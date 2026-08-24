<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;

class PlayerPoolRatingsData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?int $positionalRanking = null,
        public ?int $totalRanking = null,
        public ?float $totalRating = null,
    ) {
        //
    }
}
