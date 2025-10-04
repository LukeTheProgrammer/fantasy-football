<?php

namespace App\Services\Data;

use App\Enums\Datum;
use App\Services\Data\Sources\BaseSource;
use App\Services\Data\Sources\EspnSource;
use App\Services\Data\Sources\FantasyProsSource;
use App\Services\Data\Sources\ProFootballReferenceSource;
use App\Traits\HasDataFormats;
use Exception;
use Illuminate\Support\Arr;

class DataService
{
    use HasDataFormats;

    public function sources(string $type)
    {
        $sources = [
            Datum::SOURCE_ESPN->value         => EspnSource::class,
            Datum::SOURCE_FANTASY_PROS->value => FantasyProsSource::class,
            Datum::SOURCE_PFR->value          => ProFootballReferenceSource::class,
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

        return $this->make($sourceClass, $args);
    }

    public function espn(...$args)
    {
        $sourceClass = $this->sources(Datum::SOURCE_ESPN->value);

        return $this->make($sourceClass, $args);
    }

    public function fantasyPros(...$args)
    {
        $sourceClass = $this->sources(Datum::SOURCE_FANTASY_PROS->value);

        return $this->make($sourceClass, $args);
    }

    public function pfr(...$args)
    {
        $sourceClass = $this->sources(Datum::SOURCE_PFR->value);

        return $this->make($sourceClass, $args);
    }

    private function make(string $sourceClass, ...$args)
    {
        return (new $sourceClass($args))
            ->dataFormat($this->dataFormat)
            ->forcePull($this->forcePull);
    }
}
