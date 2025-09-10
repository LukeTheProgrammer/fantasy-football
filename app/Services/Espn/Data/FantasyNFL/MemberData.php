<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Data\Casts\CollectionCast;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCast;

class MemberData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?string $id = null,
        public ?string $displayName = null,
        public ?string $firstName = null,
        public ?string $lastName = null,

        #[WithCast(CollectionCast::class)]
        public array|Collection $notificationSettings = [],
    ) {
        //
    }
}
