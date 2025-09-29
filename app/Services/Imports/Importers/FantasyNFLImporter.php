<?php

namespace App\Services\Imports\Importers;

use App\Models\League;
use App\Models\User;
use App\Services\Imports\Drivers\FantasyNFL\BaseFantasyNFLDriver;
use Illuminate\Support\Collection;

class FantasyNFLImporter
{
    public function __construct(public ?BaseFantasyNFLDriver $driver = null)
    {
        //
    }

    public function setConfig(array|Collection $config)
    {
        $this->driver->setConfig($config);
    }

    public function importLeague(?User $creator = null, ?array $credentials = []): League
    {
        return $this->driver->importLeague($creator, $credentials);
    }

    public function importRosters(League $league, int $year): League
    {
        return $this->driver->importRosters($league, $year);
    }
}
