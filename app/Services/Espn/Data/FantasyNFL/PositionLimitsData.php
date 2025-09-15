<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\BaseData;
use App\Services\Espn\EspnConstants;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class PositionLimitsData extends BaseData
{
    public function __construct(
        public ?int $QB = 0,
        public ?int $RB = 0,
        public ?int $WR = 0,
        public ?int $TE = 0,
        public ?int $DST = 0,
        public ?int $K = 0,
        public ?int $P = 0,
        public ?int $DT = 0,
        public ?int $DE = 0,
        public ?int $LB = 0,
        public ?int $CB = 0,
        public ?int $S = 0,
        public ?int $HC = 0,
    ) {
        //
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        $limits = [];

        foreach (EspnConstants::POSITION_LIMIT_MAP as $i => $pos) {
            $limits[$pos] = Arr::get($value, $i, 0);
        }

        return static::from($limits);
    }
}
