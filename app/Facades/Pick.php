<?php

namespace App\Facades;

use App\Models\Draft;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array onTheClock(Draft $draft, int $upcoming = 8)
 * @method static Collection board(Draft $draft, float $ppr, bool $superflex)
 * @method static Collection rosters(Draft $draft)
 */
class Pick extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Pick';
    }
}
