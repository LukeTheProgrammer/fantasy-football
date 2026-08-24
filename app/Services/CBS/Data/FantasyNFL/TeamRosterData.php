<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class TeamRosterData extends BaseData
{
    public function __construct(
        public ?float $appliedStatTotal = null,
        public ?int $tradeReservedEntries = null,

        #[WithCast(TeamRosterEntryData::class)]
        public array|Collection $entries = [],
    ) {
        //
    }
}
