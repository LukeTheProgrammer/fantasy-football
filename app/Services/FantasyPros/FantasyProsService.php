<?php

namespace App\Services\FantasyPros;

use App\Services\FantasyPros\Resources\ProjectionsResource;
use App\Services\FantasyPros\Resources\RankingsResource;

class FantasyProsService
{
    public function projections(): ProjectionsResource
    {
        return new ProjectionsResource;
    }

    public function rankings(): RankingsResource
    {
        return new RankingsResource;
    }
}
