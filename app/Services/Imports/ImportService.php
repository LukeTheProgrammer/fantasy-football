<?php

namespace App\Services\Imports;

use App\Enums\Datum;
use App\Enums\FantasyPlatforms;
use App\Services\Imports\Drivers\Rankings\FantasyProsRankingsDriver;
use App\Services\Imports\Drivers\Projections\FantasyProsProjectionsDriver;
use App\Services\Imports\Drivers\FantasyNFL\EspnDriver;
use App\Services\Imports\Drivers\NFL\EspnNFLDriver;
use App\Services\Imports\Importers\DraftRankingsImporter;
use App\Services\Imports\Importers\FantasyNFLImporter;
use App\Services\Imports\Importers\NFLImporter;
use App\Services\Imports\Importers\ProjectionsImporter;
use Exception;
use Illuminate\Support\Arr;

class ImportService
{
    public function importDrivers(string $type)
    {
        $drivers = [
            'draft_rankings' => [
                Datum::SOURCE_FANTASY_PROS->value => FantasyProsRankingsDriver::class,
            ],
            'fantasy_nfl' => [
                FantasyPlatforms::ESPN->value => EspnDriver::class,
            ],
            'nfl' => [
                FantasyPlatforms::ESPN->value => EspnNFLDriver::class,
            ],
            'projections' => [
                Datum::SOURCE_FANTASY_PROS->value => FantasyProsProjectionsDriver::class,
            ],
        ];

        return Arr::get($drivers, $type, []);
    }

    /**
     * Draft Rankings Import
     *
     * @param string $driver
     * @param mixed ...$args
     *
     * @return DraftRankingsImporter
     */
    public function draftRankings(string $driver, ...$args)
    {
        $driverClass = Arr::get($this->importDrivers('draft_rankings'), $driver, false);

        if (! $driverClass) {
            throw new Exception('Invalid driver: ' . $driver);
        }

        $driver = new $driverClass(...$args);

        return new DraftRankingsImporter($driver);
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
        $driver = (! $driver instanceof FantasyPlatforms) ? FantasyPlatforms::from($driver) : $driver;

        $driverClass = Arr::get($this->importDrivers('fantasy_nfl'), $driver->value, false);

        if (! $driverClass) {
            throw new Exception('Invalid driver: ' . $driver);
        }

        $driver = new $driverClass(...$args);

        return new FantasyNFLImporter($driver);
    }

    /**
     * Projections Import
     *
     * @param string $driver
     * @param mixed ...$args
     *
     * @return ProjectionsImporter
     */
    public function projections(string $driver, ...$args)
    {
        $driverClass = Arr::get($this->importDrivers('projections'), $driver, false);

        if (! $driverClass) {
            throw new Exception('Invalid driver: ' . $driver);
        }

        $driver = new $driverClass(...$args);

        return new ProjectionsImporter($driver);
    }

    /**
     * NFL Import
     *
     * @param string $driver
     * @param mixed ...$args
     *
     * @return NFLImporter
     */
    public function nfl(string $driver, ...$args)
    {
        $driverClass = Arr::get($this->importDrivers('nfl'), $driver, false);

        if (! $driverClass) {
            throw new Exception('Invalid driver: ' . $driver);
        }

        $driver = new $driverClass(...$args);

        return new NFLImporter($driver);
    }
}
