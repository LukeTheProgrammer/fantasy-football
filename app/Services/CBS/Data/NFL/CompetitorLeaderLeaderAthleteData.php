<?php

namespace App\Services\CBS\Data\NFL;

use App\Data\Casts\CollectionCast;
use App\Services\CBS\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class CompetitorLeaderLeaderAthleteData extends BaseData
{
    public function __construct(
        public ?string $id = null,
        public ?string $lastName = null,
        public ?string $displayName = null,
        public ?string $shortName = null,

        #[WithCast(CollectionCast::class)]
        public array|Collection $links = [],
    ) {
        //
    }
}
