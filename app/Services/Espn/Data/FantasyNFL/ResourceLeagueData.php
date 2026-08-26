<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Data\Casts\CollectionCast;
use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ResourceLeagueData extends BaseData
{
    public function __construct(
        public int|string|null $id = null,
        public ?int $gameId = null,
        public ?int $scoringPeriodId = null,
        public ?int $seasonId = null,
        public ?int $segmentId = null,

        #[WithCast(DraftDetailData::class)]
        public array|DraftDetailData $draftDetail = [],

        #[WithCast(LeagueMemberData::class)]
        public array|Collection $members = [],

        #[WithCast(LeaguePlayerData::class)]
        public array|Collection $players = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $positionAgainstOpponent = [],

        #[WithCast(SettingsSettingsData::class)]
        public array|SettingsSettingsData $settings = [],

        #[WithCast(ScheduleData::class)]
        public array|Collection $schedule = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $status = [],

        #[WithCast(ResourceTeamsData::class)]
        public array|Collection $teams = [],
    ) {
        //
    }
}
