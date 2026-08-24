<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;

class ScheduleItemData extends BaseData
{
    public function __construct(
        public ?int $adjustment = null,
        public ?int $gamesPlayed = null,
        public ?int $teamId = null,
        public ?int $tiebreak = null,
        public ?float $totalPoints = null,
    ) {
        //
    }
}
