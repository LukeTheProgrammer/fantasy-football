<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\Resources\Scrapers\NflTeamRoster;

class Scrapers extends BaseResource
{
    public function getRoster(string $teamName)
    {
        return (new NflTeamRoster())->get($teamName);
    }
}
