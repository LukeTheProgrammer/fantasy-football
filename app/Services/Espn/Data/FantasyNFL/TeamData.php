<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\BaseData;
use Spatie\LaravelData\Attributes\WithCast;

class TeamData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public int|string|null $id = null,

        #[WithCast(TeamRosterData::class)]
        public array|TeamRosterData $roster = [],
    ) {
        //
    }
}
