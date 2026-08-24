<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;
use Spatie\LaravelData\Attributes\WithCast;

class RosterSettingsData extends BaseData
{
    public function __construct(
        public ?bool $isBenchUnlimited = null,
        public ?bool $isUsingUndroppableList = null,
        public ?int $moveLimit = null,
        public ?string $lineupLocktimeType = null,
        public ?string $rosterLocktimeType = null,
        public array $lineupSlotStatLimits = [],
        public array $universeIds = [],

        #[WithCast(LineupSlotCountsData::class)]
        public array|LineupSlotCountsData $lineupSlotCounts = [],

        #[WithCast(PositionLimitsData::class)]
        public array|PositionLimitsData $positionLimits = [],
    ) {
        //
    }
}
