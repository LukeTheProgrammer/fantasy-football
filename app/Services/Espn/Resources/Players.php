<?php

namespace App\Services\Espn\Resources;

use App\Facades\Espn;
use Illuminate\Support\Facades\Http;

class Players
{
    public function getPlayer(int|string $playerId)
    {
        // http://sports.core.api.espn.com/v2/sports/football/leagues/nfl/seasons/2025/athletes/4572380?lang=en&region=us
        $url = Espn::BASE_URL . '/v2/sports/football/leagues/nfl/seasons/2025/athletes/' . $playerId . '?lang=en&region=us';

        $response = Http::get($url);

        return $response->json();
    }

    public function getTeamPlayers(int|string $teamId, int $page = 1)
    {
        // https://sports.core.api.espn.com/v2/sports/football/leagues/nfl/seasons/2025/teams/1/athletes?lang=en&region=us&page=1
        $url = Espn::BASE_URL . '/v2/sports/football/leagues/nfl/seasons/2025/teams/' . $teamId . '/athletes?lang=en&region=us&page=' . $page;

        echo $url . PHP_EOL;

        $response = Http::get($url);

        return $response->json();
    }
}
