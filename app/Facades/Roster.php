<?php

namespace App\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array slotLineup(array $template, Collection $squad)
 * @method static array reserveSlots()
 */
class Roster extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Roster';
    }
}
