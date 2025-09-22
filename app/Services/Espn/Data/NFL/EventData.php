<?php

namespace App\Services\Espn\Data\NFL;

use App\Data\Casts\CarbonCast;
use App\Data\Casts\CollectionCast;
use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class EventData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $shortName = null,
        public ?bool $timeValid = null,

        #[WithCast(CarbonCast::class)]
        public string|Carbon|null $date = null,

        #[WithCast(CompetitionData::class)]
        public array|Collection $competitions = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $links = [],

        #[WithCast(EventSeasonData::class)]
        public array|EventSeasonData $season = [],

        #[WithCast(EventSeasonTypeData::class)]
        public array|EventSeasonTypeData $seasonType = [],

        #[WithCast(EventWeekData::class)]
        public array|EventWeekData $week = [],
    ) {
        //
    }
}
