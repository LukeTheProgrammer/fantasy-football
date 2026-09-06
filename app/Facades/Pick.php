<?php

namespace App\Facades;

use App\Models\Draft;
use App\Models\Player;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array syncCbsPicks(Draft $draft)
 * @method static array onTheClock(Draft $draft, int $upcoming = 8)
 * @method static Collection board(Draft $draft, float $ppr, bool $superflex)
 * @method static array playerProfile(Draft $draft, Player $player, float $ppr, bool $superflex)
 * @method static Collection rosters(Draft $draft, float $ppr, bool $superflex)
 */
class Pick extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Pick';
    }
}
