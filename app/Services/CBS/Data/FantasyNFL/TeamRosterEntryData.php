<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;
use Spatie\LaravelData\Attributes\WithCast;

class TeamRosterEntryData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?int $playerId = null,
        public ?int $acquisitionDate = null,
        public ?int $lineupSlotId = null,
        public ?string $acquisitionType = null,
        public ?string $injuryStatus = null,
        public ?string $status = null,
        public ?array $pendingTransactionIds = null,

        #[WithCast(PlayerPoolEntryData::class)]
        public array|PlayerPoolEntryData $playerPoolEntry = [],
    ) {
        //
    }
}
