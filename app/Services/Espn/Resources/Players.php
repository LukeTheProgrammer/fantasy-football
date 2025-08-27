<?php

namespace App\Services\Espn\Resources;

class Players extends BaseResource
{
    public function getPlayer(int|string $playerId)
    {
        // /v2/sports/football/leagues/nfl/seasons/2025/athletes/4572380?lang=en&region=us
        $url = $this->buildUrl('athletes/' . $playerId);

        $response = $this->get($url, $this->query());

        return $response->json();
    }
}
