<?php

namespace App\Services\CBS\Data\FantasyNFL;

use App\Services\CBS\Data\BaseData;

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
