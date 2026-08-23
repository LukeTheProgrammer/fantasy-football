<?php

namespace App\Services\Imports;

use App\Enums\Datum;
use App\Enums\FantasyPlatforms;
use App\Services\Imports\Drivers\FantasyNFL\EspnDriver;
use App\Services\Imports\Drivers\NFL\EspnNFLDriver;
use App\Services\Imports\Drivers\NFL\ProFootballReferenceDriver;
use App\Services\Imports\Importers\FantasyNFLImporter;
use App\Services\Imports\Importers\FantasyProsProjectionsImporter;
use App\Services\Imports\Importers\FantasyProsRankingsImporter;
use Exception;
use Illuminate\Support\Arr;

class ImportService
{
    public function importDrivers(string $type)
    {
        $drivers = [
            'fantasy_nfl' => [
                FantasyPlatforms::ESPN->value => EspnDriver::class,
            ],
            'nfl' => [
                FantasyPlatforms::ESPN->value => EspnNFLDriver::class,
                Datum::SOURCE_PFR->value      => ProFootballReferenceDriver::class,
            ],
        ];

        return Arr::get($drivers, $type, []);
    }

    /**
     * Fantasy NFL Import
     *
     * @param string $driver
     * @param mixed ...$args
     *
     * @return FantasyNFLImporter
     */
    public function fantasyNFL(string|FantasyPlatforms $driver, ...$args)
    {
        $driver = (!$driver instanceof FantasyPlatforms) ? FantasyPlatforms::from($driver) : $driver;

        $driverClass = Arr::get($this->importDrivers('fantasy_nfl'), $driver->value, false);

        if (!$driverClass) {
            throw new Exception('Invalid driver: ' . $driver);
        }

        $driver = new $driverClass(...$args);

        return new FantasyNFLImporter($driver);
    }

    /**
     * FantasyPros Projections Import
     *
     * Reads slates the fetch stage already archived, so it takes no driver.
     *
     * @return FantasyProsProjectionsImporter
     */
    public function fantasyProsProjections()
    {
        return new FantasyProsProjectionsImporter;
    }

    /**
     * FantasyPros Draft Rankings Import
     *
     * Reads boards the fetch stage already archived, so it takes no driver.
     *
     * @return FantasyProsRankingsImporter
     */
    public function fantasyProsRankings()
    {
        return new FantasyProsRankingsImporter;
    }
}
