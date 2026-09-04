<?php

namespace App\Services\CBS\Resources\FantasyNFL;

class GetDetails extends FantasyNFLResource
{
    public function path(): string
    {
        return 'league/details';
    }
}
