<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Data\Casts\CollectionCast;
use App\Services\Espn\EspnConstants;
use Illuminate\Support\Arr;
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

        #[WithCast(PlayerStatsData::class)]
        public array|Collection $stats = [],
    ) {
        //
    }

    public function getStat(string $key): ?PlayerStatsData
    {
        $statId = Arr::get(EspnConstants::PLAYER_STAT_IDS, $key);

        return $this->stats->firstWhere('id', $statId);
    }

    public function getProjectedWeekPoints(): ?PlayerStatsData
    {
        return $this->getStat('projected_week_points');
    }
}
