<?php

namespace App\Services\Espn\Data\NflFantasyLeagues;

use Spatie\LaravelData\Attributes\WithCast;

class TeamData extends BaseData
{
    public bool $isCollectionCast = true;

    public function __construct(
        public ?int $id = null,

        #[WithCast(TeamRosterData::class)]
        public array|TeamRosterData $roster = [],
    ) {
        //
    }
}
