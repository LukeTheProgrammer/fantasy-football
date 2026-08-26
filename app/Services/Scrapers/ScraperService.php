<?php

namespace App\Services\Scrapers;

use App\Enums\Datum;
use App\Services\Scrapers\Resources\BaseScraperResource;
use App\Services\Scrapers\Resources\Espn;
use App\Services\Scrapers\Resources\ProFootballReference;
use Exception;
use Illuminate\Support\Arr;

class ScraperService
{
    public function resources(string $type)
    {
        $resources = [
            Datum::SOURCE_ESPN->value => Espn::class,
            Datum::SOURCE_PFR->value  => ProFootballReference::class,
        ];

        return Arr::get($resources, $type, []);
    }

    /**
     * Draft Rankings Import
     *
     * @param string $resource
     * @param mixed ...$args
     *
     * @return BaseScraperResource
     */
    public function scraper(string $resource, ...$args)
    {
        $resourceClass = $this->resources($resource);

        if (!$resourceClass) {
            throw new Exception('Invalid resource: ' . $resource);
        }

        return new $resourceClass($args);
    }
}
