<?php

namespace App\Services\Espn\Data\NFL;

use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class CompetitorData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?string $id = null,
        public ?bool $winner = null,
        public ?int $order = null,
        public ?string $homeAway = null,
        public ?string $type = null,

        #[WithCast(CompetitorTeamData::class)]
        public array|CompetitorTeamData $team = [],

        #[WithCast(CompetitorScoreData::class)]
        public array|CompetitorScoreData $score = [],

        #[WithCast(CompetitorLeaderData::class)]
        public array|Collection $leaders = [],

        #[WithCast(CompetitorRecordData::class)]
        public array|Collection $record = [],
    ) {
        //
    }
}
