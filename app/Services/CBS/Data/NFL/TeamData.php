<?php

namespace App\Services\CBS\Data\NFL;

use App\Data\Casts\CollectionCast;
use App\Services\CBS\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class TeamData extends BaseData
{
    public function __construct(
        public ?string $id = null,
        public ?string $abbreviation = null,
        public ?string $clubhouse = null,
        public ?string $color = null,
        public ?string $displayName = null,
        public ?string $location = null,
        public ?string $logo = null,
        public ?string $name = null,
        public ?string $recordSummary = null,
        public ?string $seasonSummary = null,
        public ?string $standingSummary = null,

        #[WithCast(CollectionCast::class)]
        public array|Collection $groups = [],
    ) {
        //
    }
}
