<?php

namespace App\Services\CBS\Resources\FantasyNFL;

class GetOwners extends FantasyNFLResource
{
    public function path(): string
    {
        return 'league/owners';
    }
}
