<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Espn extends Facade
{
    const BASE_URL = 'https://sports.core.api.espn.com';

    public static function getFacadeAccessor()
    {
        return 'Espn';
    }
}
