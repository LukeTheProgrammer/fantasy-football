<?php

namespace App\Services\CBS\Resources\FantasyNFL;

class GetDraftConfig extends FantasyNFLResource
{
    public function path(): string
    {
        return 'league/draft/config';
    }
}
