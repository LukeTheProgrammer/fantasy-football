<?php

namespace App\Services\Espn\Data\NFL;

use App\Data\Casts\CarbonCast;
use App\Data\Casts\CollectionCast;
use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class CompetitionData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?string $id = null,
        public ?int $attendance = null,
        public ?bool $timeValid = null,
        public ?bool $neutralSite = null,
        public ?bool $boxscoreAvailable = null,
        public ?bool $ticketsAvailable = null,

        #[WithCast(CarbonCast::class)]
        public string|Carbon|null $date = null,

        #[WithCast(CarbonCast::class)]
        public string|Carbon|null $timestamp = null,

        #[WithCast(BroadcastData::class)]
        public array|Collection $broadcasts = [],

        #[WithCast(CompetitorData::class)]
        public array|Collection $competitors = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $notes = [],

        #[WithCast(CompetitionStatusData::class)]
        public array|CompetitionStatusData $status = [],

        #[WithCast(CompetitionTypeData::class)]
        public array|CompetitionTypeData $type = [],

        #[WithCast(CompetitionVenueData::class)]
        public array|CompetitionVenueData $venue = [],
    ) {
        // $this->timestamp = (is_string($this->timestamp))
        //     ? Carbon::parse($this->timestamp)
        //     : $this->timestamp;

        // $this->date = (is_string($this->date))
        //     ? Carbon::parse($this->date)
        //     : $this->date;
    }
}
