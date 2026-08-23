<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Data\Casts\CollectionCast;
use App\Services\CBS\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class LeagueTeamData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public int|string|null $id,
        public ?string $abbrev,

        #[WithCast(CollectionCast::class)]
        public array|Collection $owners = [],
    ) {
        //
    }
}
