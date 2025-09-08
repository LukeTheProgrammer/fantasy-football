<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

use App\Services\Espn\Data\Casts\CollectionCast;
use App\Services\Espn\Data\NflFantasyLeagues\DraftDetailData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ResourceSettingsData extends BaseData
{
    public function __construct(
        public ?int $gameId = null,
        public ?int $scoringPeriodId = null,
        public ?int $seasonId = null,
        public ?int $segmentId = null,

        #[WithCast(DraftDetailData::class)]
        public array|DraftDetailData $draftDetail = [],

        #[WithCast(SettingsSettingsData::class)]
        public array|SettingsSettingsData $settings = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $status = [],
    ) {
        //
    }
}
