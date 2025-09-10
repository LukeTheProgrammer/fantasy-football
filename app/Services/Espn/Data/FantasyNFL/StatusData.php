<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\Casts\CollectionCast;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class StatusData extends BaseData
{
    public function __construct(
        public ?bool $isActive = null,
        public ?bool $isExpired = null,
        public ?bool $isFull = null,
        public ?bool $isPlayoffMatchupEdited = null,
        public ?bool $isToBeDeleted = null,
        public ?bool $isViewable = null,
        public ?bool $isWaiverOrderEdited = null,
        public ?int $activatedDate = null,
        public ?int $createdAsLeagueType = null,
        public ?int $currentLeagueType = null,
        public ?int $currentMatchupPeriod = null,
        public ?int $finalScoringPeriod = null,
        public ?int $firstScoringPeriod = null,
        public ?int $latestScoringPeriod = null,
        public ?int $standingsUpdateDate = null,
        public ?int $teamsJoined = null,
        public ?int $transactionScoringPeriod = null,
        public ?int $waiverLastExecutionDate = null,

        #[WithCast(CollectionCast::class)]
        public array|Collection $previousSeasons = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $waiverProcessStatus = [],
    ) {
        //
    }
}
