<?php

namespace App\Services\CBS\Resources\FantasyNFL;

class GetDraftOrder extends FantasyNFLResource
{
    public function path(): string
    {
        return 'league/draft/order';
    }
}
