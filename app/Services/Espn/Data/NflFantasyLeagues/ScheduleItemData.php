<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

class ScheduleItemData extends BaseData
{
    public function __construct(
        public ?int $gamesPlayed = null,
        public ?int $teamId = null,
        public ?int $totalPoints = null,
    ) {
        //
    }
}
