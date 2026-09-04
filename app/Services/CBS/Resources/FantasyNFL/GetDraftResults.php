<?php

namespace App\Services\CBS\Resources\FantasyNFL;

class GetDraftResults extends FantasyNFLResource
{
    public function path(): string
    {
        return 'league/draft/results';
    }
}
