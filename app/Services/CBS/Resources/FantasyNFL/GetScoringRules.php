<?php

namespace App\Services\CBS\Resources\FantasyNFL;

class GetScoringRules extends FantasyNFLResource
{
    public function path(): string
    {
        return 'league/scoring/rules';
    }
}
