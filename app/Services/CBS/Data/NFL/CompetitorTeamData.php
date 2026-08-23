<?php

namespace App\Services\CBS\Data\NFL;

use App\Data\Casts\CollectionCast;
use App\Services\CBS\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class CompetitorTeamData extends BaseData
{
    public function __construct(
        public ?string $id = null,
        public ?string $location = null,
        public ?string $nickname = null,
        public ?string $abbreviation = null,
        public ?string $displayName = null,
        public ?string $shortDisplayName = null,

        #[WithCast(CollectionCast::class)]
        public array|Collection $logos = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $links = [],
    ) {
        //
    }
}
