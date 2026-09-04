<?php

namespace App\Services\CBS\Resources\FantasyNFL;

class GetRules extends FantasyNFLResource
{
    public function path(): string
    {
        return 'league/rules';
    }
}
