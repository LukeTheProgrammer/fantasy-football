<?php

namespace App\Services\Imports;

use App\Enums\RankingSourcesEnum;
use App\Enums\FantasyPlatformsEnum;
use App\Services\Imports\Drivers\Rankings\FantasyProsDriver;
use App\Services\Imports\Drivers\FantasyNFL\EspnDriver;
use App\Services\Imports\Importers\DraftRankingsImporter;
use App\Services\Imports\Importers\FantasyNFLImporter;
use Exception;
use Illuminate\Support\Arr;

class ImportService
{
    public function importDrivers(string $type)
    {
        $drivers = [
            'draft_rankings' => [
                RankingSourcesEnum::FANTASY_PROS->value => FantasyProsDriver::class,
            ],
            'fantasy_nfl' => [
                FantasyPlatformsEnum::ESPN->value => EspnDriver::class,
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
     * @return DraftRankingsImporter
     */
    public function fantasyNFL(string|FantasyPlatformsEnum $driver, ...$args)
    {
        $driver = (! $driver instanceof FantasyPlatformsEnum) ? FantasyPlatformsEnum::from($driver) : $driver;

        $driverClass = Arr::get($this->importDrivers('fantasy_nfl'), $driver->value, false);

        if (! $driverClass) {
            throw new Exception('Invalid driver: ' . $driver);
        }

        $driver = new $driverClass(...$args);

        return new FantasyNFLImporter($driver);
    }
}
