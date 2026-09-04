<?php

namespace App\Services\CBS\Resources\FantasyNFL;

class GetKeepers extends FantasyNFLResource
{
    public function path(): string
    {
        return 'league/keepers';
    }
}
