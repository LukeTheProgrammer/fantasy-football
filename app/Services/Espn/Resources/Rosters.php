<?php

namespace App\Services\Espn\Resources;

class Rosters extends BaseResource
{
    public function getRoster(int|string $teamId)
    {
        // v2/sports/football/nfl/teams/12/roster
        $url = $this->buildSiteUrl('teams/' . $teamId . '/roster');
        // dd($url);

        $response = $this->get($url, $this->query());

        return $response->json();
    }
}
