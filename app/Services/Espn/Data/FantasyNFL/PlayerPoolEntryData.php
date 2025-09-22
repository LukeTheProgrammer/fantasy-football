<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class PlayerPoolEntryData extends BaseData
{
    public function __construct(
        public int|string|null $id = null,
        public ?bool $lineupLocked = null,
        public ?bool $rosterLocked = null,
        public ?bool $tradeLocked = null,
        public ?float $appliedStatTotal = null,
        public ?float $draftAuctionValue = null,
        public ?float $keeperValue = null,
        public ?float $keeperValueFuture = null,
        public ?int $onTeamId = null,
        public ?string $status = null,

        #[WithCast(PlayerPoolRatingsData::class)]
        public array|Collection $ratings = [],

        #[WithCast(PlayerData::class)]
        public array|PlayerData $player = [],
    ) {
        //
    }
}
