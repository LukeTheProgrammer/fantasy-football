<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\BaseData;
use App\Data\Casts\CollectionCast;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class PlayerStatsData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?int $id = null,
        public ?bool $isActual = null,
        public ?bool $isProjected = null,
        public ?int $externalId = null,
        public ?int $proTeamId = null,
        public ?int $scoringPeriodId = null,
        public ?int $seasonId = null,
        public ?int $statSourceId = null,
        public ?int $statSplitTypeId = null,
        public ?float $appliedTotal = null,

        #[WithCast(CollectionCast::class)]
        public array|Collection $appliedStats = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $stats = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $variance = [],
    ) {
        $this->isActual = $this->statSourceId === 0;
        $this->isProjected = $this->statSourceId === 1;
    }
}
