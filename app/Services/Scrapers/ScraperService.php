<?php

namespace App\Services\Scrapers;

use App\Enums\DataSources;
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
            DataSources::ESPN->value                   => Espn::class,
            DataSources::PFR->value => ProFootballReference::class,
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

        if (! $resourceClass) {
            throw new Exception('Invalid resource: ' . $resource);
        }

        return new $resourceClass($args);
    }
}
