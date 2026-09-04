<?php

namespace App\Facades;

use App\Models\League;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array format(League $league)
 */
class Ranking extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Ranking';
    }
}
