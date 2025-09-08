<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ResourceTeamData extends BaseData
{
    public function __construct(
        public ?int $id = null,
        public ?int $gameId = null,
        public ?int $scoringPeriodId = null,
        public ?int $seasonId = null,
        public ?int $segmentId = null,

        #[WithCast(DraftDetailData::class)]
        public array|DraftDetailData $draftDetail = [],

        #[WithCast(MemberData::class)]
        public array|Collection $members = [],

        #[WithCast(StatusData::class)]
        public array|StatusData $status = [],

        #[WithCast(TeamData::class)]
        public array|Collection $teams = [],
    ) {
        //
    }
}
