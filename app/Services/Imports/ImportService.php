<?php

namespace App\Services\Imports;

use App\Services\Imports\Drivers\Rankings\FantasyProsDriver;
use App\Services\Imports\Models\DraftRankingsImport;

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
        $drivers = [
            'fantasy-pros' => FantasyProsDriver::class,
        ];

        $driverClass = $drivers[$driver];

        $driver = new $driverClass(...$args);

        return new DraftRankingsImport($driver);
    }
}
