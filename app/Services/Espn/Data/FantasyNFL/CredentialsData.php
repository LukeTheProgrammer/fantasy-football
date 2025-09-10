<?php

namespace App\Services\Espn\Data\FantasyNFL;

class CredentialsData extends BaseData
{
    public function __construct(
        public int $leagueId,
        public ?string $s2 = '',
        public ?string $swid = '',
    ) {
        //
    }
}
