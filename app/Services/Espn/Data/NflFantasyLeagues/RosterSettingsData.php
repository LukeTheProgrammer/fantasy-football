<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

class RosterSettingsData extends BaseData
{
    public function __construct(
        public ?bool $isBenchUnlimited = null,
        public ?bool $isUsingUndroppableList = null,
        public ?int $moveLimit = null,
        public ?string $lineupLocktimeType = null,
        public ?string $rosterLocktimeType = null,
        public array $lineupSlotCounts = [],
        public array $lineupSlotStatLimits = [],
        public array $positionLimits = [],
        public array $universeIds = [],
    ) {
        //
    }
}
