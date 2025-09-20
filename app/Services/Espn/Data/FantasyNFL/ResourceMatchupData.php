<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ResourceMatchupData extends BaseData
{
    public function __construct(
        public int|string|null $id = null,
        public ?int $gameId = null,
        public ?int $scoringPeriodId = null,
        public ?int $seasonId = null,
        public ?int $segmentId = null,

        #[WithCast(DraftDetailData::class)]
        public array|DraftDetailData $draftDetail = [],

        #[WithCast(ScheduleData::class)]
        public array|Collection $schedule = [],

        #[WithCast(StatusData::class)]
        public array|StatusData $status = [],

        #[WithCast(TeamData::class)]
        public array|Collection $teams = [],
    ) {
        //
    }
}
