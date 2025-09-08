<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

use Spatie\LaravelData\Attributes\WithCast;

class TeamRosterEntryData extends BaseData
{
    public bool $isCollectionCast = true;

    public function __construct(
        public ?int $playerId = null,
        public ?int $acquisitionDate = null,
        public ?int $lineupSlotId = null,
        public ?string $acquisitionType = null,
        public ?string $injuryStatus = null,
        public ?string $pendingTransactionIds = null,
        public ?string $status = null,

        #[WithCast(PlayerPoolEntryData::class)]
        public array|PlayerPoolEntryData $playerPoolEntry = [],
    ) {
        //
    }
}
