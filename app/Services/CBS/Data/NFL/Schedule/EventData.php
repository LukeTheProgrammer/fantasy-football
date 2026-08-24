<?php

namespace App\Services\CBS\Data\NFL\Schedule;

use App\Data\Casts\CollectionCast;
use App\Services\CBS\Data\BaseData;
use App\Services\CBS\Data\NFL\CompetitionData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class EventData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?string $id = null,
        public ?string $date = null,
        public ?string $name = null,
        public ?string $shortName = null,

        #[WithCast(CompetitionData::class)]
        public array|Collection $competitions = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $links = [],

        #[WithCast(SeasonData::class)]
        public array|SeasonData $season = [],

        #[WithCast(SeasonTypeData::class)]
        public array|SeasonTypeData $seasonType = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $timeValid = [],

        #[WithCast(WeekData::class)]
        public array|WeekData $week = [],
    ) {
        //
    }
}
