<?php

namespace App\Services\Imports\Drivers\FantasyNFL;

use App\Models\League;
use App\Models\User;

abstract class BaseFantasyNFLDriver
{
    public mixed $credentials;

    abstract public function import(): League;
    abstract public function setCredentials(array $credentials);
    abstract public function setCreator(User $user);
}
