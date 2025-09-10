<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\Casts\CollectionCast;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class LeagueTeamData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?int $id = null,
        public ?string $abbrev,

        #[WithCast(CollectionCast::class)]
        public array|Collection $owners = [],
    ) {
        //
    }
}
