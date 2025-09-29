<?php

namespace App\Services\FantasyPros;

use App\Services\FantasyPros\Resources\ProjectionsResource;

class FantasyProsService
{
    public function projections(): ProjectionsResource
    {
        return new ProjectionsResource();
    }
}
