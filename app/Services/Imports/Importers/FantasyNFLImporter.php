<?php

namespace App\Services\Imports\Importers;

use App\Models\League;
use App\Models\User;
use App\Services\Imports\Drivers\Leagues\BaseLeagueDriver;

class FantasyNFLImporter
{
    public function __construct(public ?BaseLeagueDriver $driver = null)
    {
        //
    }

    public function setCredentials(array $credentials)
    {
        $this->driver->setCredentials($credentials);
    }

    public function setCreator(User $user)
    {
        $this->driver->setCreator($user);
    }

    public function import(): League
    {
        return $this->driver->import();
    }
}
