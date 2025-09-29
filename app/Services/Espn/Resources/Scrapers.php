<?php

namespace App\Services\Espn\Resources;

use App\Services\Espn\Scrapers\NFLRosters;

class Scrapers extends BaseResource
{
    public function getRoster(string $teamName)
    {
        return (new NFLRosters())->get($teamName);
    }
}
