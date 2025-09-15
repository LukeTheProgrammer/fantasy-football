<?php

namespace App\Services\Espn\Data\NFL;

use App\Data\Casts\CarbonCast;
use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ResourceTeamScheduleData extends BaseData
{
    public function __construct(
        public ?int $byeWeek = null,
        public ?string $status = null,

        #[WithCast(CarbonCast::class)]
        public string|Carbon|null $timestamp = null,

        #[WithCast(EventData::class)]
        public array|Collection $events = [],

        #[WithCast(RequestedSeasonData::class)]
        public array|RequestedSeasonData $requestedSeason = [],

        #[WithCast(SeasonData::class)]
        public array|SeasonData $season = [],

        #[WithCast(TeamData::class)]
        public array|TeamData $team = [],
    ) {
        //
    }
}
