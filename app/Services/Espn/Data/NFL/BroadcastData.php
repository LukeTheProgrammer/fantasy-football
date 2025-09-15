<?php

namespace App\Services\Espn\Data\NFL;

use App\Data\Casts\CollectionCast;
use App\Services\Espn\Data\BaseData;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class BroadcastData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?bool $partnered = null,
        public ?string $lang = null,
        public ?string $region = null,

        #[WithCast(CollectionCast::class)]
        public array|Collection $type = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $market = [],

        #[WithCast(CollectionCast::class)]
        public array|Collection $media = [],
    ) {
        //
    }
}
