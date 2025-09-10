<?php

namespace App\Services\Imports\Drivers\Leagues;

use App\Models\League;
use App\Models\User;

abstract class BaseLeagueDriver
{
    public mixed $credentials;

    abstract public function import(): League;
    abstract public function setCredentials(array $credentials);
    abstract public function setCreator(User $user);
}
