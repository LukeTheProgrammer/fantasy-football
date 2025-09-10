<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\Casts\CollectionCast;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class PlayerData extends BaseData
{
    public function __construct(
        public ?int $id = null,
        public ?bool $active = null,
        public ?bool $droppable = null,
        public ?bool $injured = null,
        public ?int $defaultPositionId = null,
        public ?int $lastNewsDate = null,
        public ?int $proTeamId = null,
        public ?int $universeId = null,
        public ?string $firstName = null,
        public ?string $fullName = null,
        public ?string $injuryStatus = null,
        public ?string $lastName = null,

        #[WithCast(CollectionCast::class)]
        public array|Collection $draftRanksByRankType = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $eligibleSlots = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $outlooks = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $ownership = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $rankings = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $seasonOutlook = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $stats = [],
    ) {
        //
    }
}
