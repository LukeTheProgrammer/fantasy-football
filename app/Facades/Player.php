<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Player extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Player';
    }
}
