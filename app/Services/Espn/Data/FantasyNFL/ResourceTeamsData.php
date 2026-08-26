<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Data\Casts\CollectionCast;
use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ResourceTeamsData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public int|string|null $id = null,
        public ?bool $isActive = null,
        public ?float $points = null,
        public ?float $pointsAdjusted = null,
        public ?float $pointsDelta = null,
        public ?int $currentProjectedRank = null,
        public ?int $divisionId = null,
        public ?int $draftDayProjectedRank = null,
        public ?int $gameId = null,
        public ?int $playoffSeed = null,
        public ?int $rankCalculatedFinal = null,
        public ?int $rankFinal = null,
        public ?int $scoringPeriodId = null,
        public ?int $seasonId = null,
        public ?int $segmentId = null,
        public ?int $waiverRank = null,
        public ?string $abbrev = null,
        public ?string $logo = null,
        public ?string $logoType = null,
        public ?string $name = null,
        public ?string $playoffClinchType = null,
        public ?string $primaryOwner = null,
        public array $currentSimulationResults = [],
        public array $draftStrategy = [],
        public array $owners = [],

        #[WithCast(DraftDetailData::class)]
        public array|DraftDetailData $draftDetail = [],

        #[WithCast(MemberData::class)]
        public array|Collection $members = [],

        #[WithCast(TeamRecordData::class)]
        public array|Collection $record = [],

        #[WithCast(TeamRosterData::class)]
        public array|TeamRosterData $roster = [],

        #[WithCast(StatusData::class)]
        public array|StatusData $status = [],

        #[WithCast(TeamData::class)]
        public array|Collection $teams = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $tradeBlock = [],

        #[WithCast(TeamTransactionData::class)]
        public array|TeamTransactionData $transactionCounter = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $valuesByStat = [],
    ) {
        //
    }
}
