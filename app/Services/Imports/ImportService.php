<?php

namespace App\Services\Imports;

use App\Enums\RankingSourcesEnum;
use App\Services\Imports\Drivers\Rankings\FantasyProsDriver;
use App\Services\Imports\Models\DraftRankingsImport;
use Exception;
use Illuminate\Support\Arr;

class ImportService
{
    /**
     * Draft Rankings Import
     *
     * @param string $driver
     * @param mixed ...$args
     *
     * @return DraftRankingsImport
     */
    public function draftRankingsImport(string $driver, ...$args)
    {
        $driverClass = Arr::get($this->importDrivers(), $driver, false);

        if (! $driverClass) {
            throw new Exception('Invalid driver: ' . $driver);
        }

        $driver = new $driverClass(...$args);

        return new DraftRankingsImport($driver);
    }

    public function importDrivers()
    {
        return [
            RankingSourcesEnum::FANTASY_PROS->value => FantasyProsDriver::class,
        ];
    }
}
