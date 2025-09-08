<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

class ScoringSettingsItemData extends BaseData
{
    public bool $isCollectionCast = true;

    public function __construct(
        public ?bool $isReverseItem = null,
        public ?int $leagueRanking = null,
        public ?int $leagueTotal = null,
        public ?int $points = null,
        public ?int $statId = null,
        public array $pointsOverrides = [],
    ) {
        //
    }
}
