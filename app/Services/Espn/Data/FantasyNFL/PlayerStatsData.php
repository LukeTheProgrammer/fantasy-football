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
        public int|string|null $id = null,
        public int|string|null $externalId = null,
        public int|string|null $proTeamId = null,
        public int|string|null $scoringPeriodId = null,
        public int|string|null $seasonId = null,
        public int|string|null $statSourceId = null,
        public int|string|null $statSplitTypeId = null,
        public ?bool $isActual = null,
        public ?bool $isProjected = null,
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
