<?php

namespace App\Services\Espn\Data\NFL;

use App\Services\Espn\Data\BaseData;
use Spatie\LaravelData\Attributes\WithCast;

class CompetitorLeaderLeaderData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?string $displayValue = null,
        public ?int $value = null,

        #[WithCast(CompetitorLeaderLeaderAthleteData::class)]
        public array|CompetitorLeaderLeaderAthleteData $athlete = [],
    ) {
        //
    }
}
