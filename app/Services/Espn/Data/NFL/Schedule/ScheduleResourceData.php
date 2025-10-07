<?php

namespace App\Services\Espn\Data\NFL\Schedule;

use App\Data\Casts\CollectionCast;
use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ScheduleResourceData extends BaseData
{
    public function __construct(
        public int|string|null $timestamp = null,
        public int|string|null $byeWeek = null,
        public ?string $status = null,

        #[WithCast(EventData::class)]
        public array|Collection $events = [],

        #[WithCast(SeasonData::class)]
        public array|SeasonData $requestedSeason = [],

        #[WithCast(SeasonData::class)]
        public array|SeasonData $season = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $team = [],
    ) {
        //
    }
}
