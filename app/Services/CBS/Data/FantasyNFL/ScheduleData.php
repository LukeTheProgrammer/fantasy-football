<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;
use Spatie\LaravelData\Attributes\WithCast;

class ScheduleData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public int|string|null $id = null,
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
