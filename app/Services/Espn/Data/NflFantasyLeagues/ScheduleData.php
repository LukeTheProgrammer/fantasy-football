<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ScheduleData extends BaseData
{
    public bool $isCollectionCast = true;

    public function __construct(
        public ?int $id = null,
        public ?int $matchupPeriodId = null,
        public ?string $winner = null,

        #[WithCast(ScheduleItemData::class)]
        public array|ScheduleItemData $away = [],

        #[WithCast(ScheduleItemData::class)]
        public array|ScheduleItemData $home = [],
    ) {
        //
    }
}
