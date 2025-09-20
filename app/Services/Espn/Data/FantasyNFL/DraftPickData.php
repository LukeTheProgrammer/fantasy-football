<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\BaseData;

class DraftPickData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public int|string|null $id = null,
        public ?bool $keeper = null,
        public ?bool $reservedForKeeper = null,
        public ?bool $tradeLocked = null,
        public ?int $autoDraftTypeId = null,
        public ?int $bidAmount = null,
        public ?int $lineupSlotId = null,
        public ?int $nominatingTeamId = null,
        public ?int $overallPickNumber = null,
        public ?int $playerId = null,
        public ?int $roundId = null,
        public ?int $roundPickNumber = null,
        public ?int $teamId = null,
        public ?string $memberId = null,
    ) {
        //
    }
}
