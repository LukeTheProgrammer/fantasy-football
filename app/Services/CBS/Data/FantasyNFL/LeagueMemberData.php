<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;

class LeagueMemberData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public int|string|null $id = null,
        public ?bool $isLeagueCreator = null,
        public ?bool $isLeagueManager = null,
        public ?string $displayName = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
    ) {
        //
    }
}
