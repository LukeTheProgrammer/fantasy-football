<?php

namespace App\Services\Espn\Data\FantasyNFL;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class ScheduleData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?int $id = null,
        public ?int $matchupPeriodId = null,
        public ?string $playoffTierType = null,
        public ?string $winner = null,

        #[WithCast(ScheduleItemData::class)]
        public array|ScheduleItemData $away = [],

        #[WithCast(ScheduleItemData::class)]
        public array|ScheduleItemData $home = [],
    ) {
        //
    }
}
