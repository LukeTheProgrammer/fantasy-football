<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Scraper extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Scraper';
    }
}
