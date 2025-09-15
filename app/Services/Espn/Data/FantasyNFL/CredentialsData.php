<?php

namespace App\Services\Espn\Data\FantasyNFL;

use App\Services\Espn\Data\BaseData;

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
