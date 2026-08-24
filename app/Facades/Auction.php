<?php

namespace App\Facades;

use App\Models\Draft;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection cheatSheet(Draft $draft)
 * @method static Collection marketValues(Draft $draft)
 * @method static Collection projectedValues(Draft $draft)
 * @method static Collection teams(Draft $draft)
 */
class Auction extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Auction';
    }
}
