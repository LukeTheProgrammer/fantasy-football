<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\BaseData;
use App\Services\Espn\EspnConstants;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class LineupSlotCountsData extends BaseData
{
    protected const BENCH_POSITIONS = ['BE', 'IR'];

    public function __construct(
        public ?int $QB = 0,
        public ?int $RB = 0,
        public ?int $WR = 0,
        public ?int $TE = 0,
        public ?int $RB_WR_TE = 0,
        public ?int $DST = 0,
        public ?int $K = 0,
        public ?int $RB_WR = 0,
        public ?int $WR_TE = 0,
        public ?int $TQB = 0,
        public ?int $OP = 0,
        public ?int $DT = 0,
        public ?int $DE = 0,
        public ?int $LB = 0,
        public ?int $DL = 0,
        public ?int $CB = 0,
        public ?int $S = 0,
        public ?int $DB = 0,
        public ?int $DP = 0,
        public ?int $P = 0,
        public ?int $HC = 0,
        public ?int $ER = 0,
        public ?int $Rookie = 0,
        public ?int $BE = 0,
        public ?int $IR = 0,
    ) {
        //
    }

    public function getPositionCount()
    {
        return array_sum($this->toArray());
    }

    public function getStartersCount()
    {
        return array_sum(
            array_filter(
                $this->toArray(),
                fn ($v, $k) => $v > 0 && !in_array($k, self::BENCH_POSITIONS),
                ARRAY_FILTER_USE_BOTH
            )
        );
    }

    public function getBenchCount()
    {
        return array_sum(
            array_filter(
                $this->toArray(),
                fn ($v, $k) => $v > 0 && in_array($k, self::BENCH_POSITIONS),
                ARRAY_FILTER_USE_BOTH
            )
        );
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        $slots = [];

        foreach (EspnConstants::POSITION_SLOT_MAP as $i => $pos) {
            $slots[$pos] = Arr::get($value, $i, 0);
        }

        return static::from($slots);
    }
}
