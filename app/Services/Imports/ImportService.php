<?php

namespace App\Services\Imports;

use App\Enums\RankingSourcesEnum;
use App\Enums\FantasyPlatformsEnum;
use App\Services\Imports\Drivers\Rankings\FantasyProsRankingsDriver;
use App\Services\Imports\Drivers\Projections\FantasyProsProjectionsDriver;
use App\Services\Imports\Drivers\FantasyNFL\EspnDriver;
use App\Services\Imports\Importers\DraftRankingsImporter;
use App\Services\Imports\Importers\FantasyNFLImporter;
use App\Services\Imports\Importers\PlayerProjectionsImporter;
use Exception;
use Illuminate\Support\Arr;

class ImportService
{
    public function importDrivers(string $type)
    {
        $drivers = [
            'draft_rankings' => [
                RankingSourcesEnum::FANTASY_PROS->value => FantasyProsRankingsDriver::class,
            ],
            'fantasy_nfl' => [
                FantasyPlatformsEnum::ESPN->value => EspnDriver::class,
            ],
            'player_projections' => [
                RankingSourcesEnum::FANTASY_PROS->value => FantasyProsProjectionsDriver::class,
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

    /**
     * Player Projections Import
     *
     * @param string $driver
     * @param mixed ...$args
     *
     * @return PlayerProjectionsImporter
     */
    public function playerProjections(string $driver, ...$args)
    {
        $driverClass = Arr::get($this->importDrivers('player_projections'), $driver, false);

        if (! $driverClass) {
            throw new Exception('Invalid driver: ' . $driver);
        }

        $driver = new $driverClass(...$args);

        return new PlayerProjectionsImporter($driver);
    }
}
