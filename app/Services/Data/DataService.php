<?php

namespace App\Services\Data;

use App\Enums\DataSources;
use App\Services\Data\Sources\BaseSource;
use App\Services\Data\Sources\EspnSource;
use App\Services\Data\Sources\FantasyProsSource;
use App\Services\Data\Sources\ProFootballReferenceSource;
use Exception;
use Illuminate\Support\Arr;

class DataService
{
    public function sources(string $type)
    {
        $sources = [
            DataSources::ESPN->value         => EspnSource::class,
            DataSources::FANTASY_PROS->value => FantasyProsSource::class,
            DataSources::PFR->value          => ProFootballReferenceSource::class,
        ];

        return Arr::get($sources, $type, false);
    }

    /**
     * Draft Rankings Import
     *
     * @param string $source
     * @param mixed ...$args
     *
     * @return BaseSource
     */
    public function source(string $source, ...$args)
    {
        $sourceClass = $this->sources($source);

        if (! $sourceClass) {
            throw new Exception('Invalid source: ' . $source);
        }

        return new $sourceClass($args);
    }

    public function espn(...$args)
    {
        $sourceClass = $this->sources(DataSources::ESPN->value);

        return new $sourceClass($args);
    }

    public function fantasyPros(...$args)
    {
        $sourceClass = $this->sources(DataSources::FANTASY_PROS->value);

        return new $sourceClass($args);
    }

    public function pfr(...$args)
    {
        $sourceClass = $this->sources(DataSources::PFR->value);

        return new $sourceClass($args);
    }
}
