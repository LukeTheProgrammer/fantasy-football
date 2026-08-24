<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;
use Spatie\LaravelData\Attributes\WithCast;

class CompetitionStatusData extends BaseData
{
    public function __construct(
        public ?bool $isTBDFlex = null,
        public ?int $clock = null,
        public ?int $period = null,
        public ?string $displayClock = null,

        #[WithCast(CompetitionStatusTypeData::class)]
        public array|CompetitionStatusTypeData $type = [],
    ) {
        //
    }
}
