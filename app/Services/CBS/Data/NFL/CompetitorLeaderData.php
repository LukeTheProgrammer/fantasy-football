<?php

namespace App\Services\CBS\Data\NFL;

use App\Services\CBS\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class CompetitorLeaderData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?string $name = null,
        public ?string $displayName = null,
        public ?string $abbreviation = null,

        #[WithCast(CompetitorLeaderLeaderData::class)]
        public array|Collection $leaders = [],
    ) {
        //
    }
}
