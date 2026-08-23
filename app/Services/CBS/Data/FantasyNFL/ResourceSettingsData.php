<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Data\Casts\CollectionCast;
use App\Services\CBS\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ResourceSettingsData extends BaseData
{
    public function __construct(
        public int|string|null $id = null,
        public ?int $gameId = null,
        public ?int $scoringPeriodId = null,
        public ?int $seasonId = null,
        public ?int $segmentId = null,

        #[WithCast(CollectionCast::class)]
        public array|Collection $acquisitionSettings = [],

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
