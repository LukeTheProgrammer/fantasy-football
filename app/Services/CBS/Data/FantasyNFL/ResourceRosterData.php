<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;
use App\Data\Casts\CollectionCast;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ResourceRosterData extends BaseData
{
    public function __construct(
        public ?int $gameId = null,
        public ?int $scoringPeriodId = null,
        public ?int $seasonId = null,
        public ?int $segmentId = null,

        #[WithCast(DraftDetailData::class)]
        public array|DraftDetailData $draftDetail = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $status = [],

        #[WithCast(TeamData::class)]
        public array|Collection $teams = [],
    ) {
        //
    }
}
