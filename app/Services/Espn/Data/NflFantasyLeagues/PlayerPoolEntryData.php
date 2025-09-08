<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

use App\Services\Espn\Data\NflFantasyLeagues\PlayerData;
use Spatie\LaravelData\Attributes\WithCast;

class PlayerPoolEntryData extends BaseData
{
    public function __construct(
        public ?int $id = null,
        public ?bool $lineupLocked = null,
        public ?bool $rosterLocked = null,
        public ?bool $tradeLocked = null,
        public ?int $appliedStatTotal = null,
        public ?int $keeperValue = null,
        public ?int $keeperValueFuture = null,
        public ?int $onTeamId = null,
        public ?string $status = null,
        public array $ratings = [],

        #[WithCast(PlayerData::class)]
        public array|PlayerData $player = [],
    ) {
        //
    }
}
