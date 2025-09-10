<?php

namespace App\Services\Espn\Data\FantasyNFL;

class LeagueMemberData extends BaseData
{
    protected bool $isCollectionCast = true;

    public function __construct(
        public ?string $id = null,
        public ?bool $isLeagueCreator = null,
        public ?bool $isLeagueManager = null,
        public ?string $displayName = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
    ) {
        //
    }
}
