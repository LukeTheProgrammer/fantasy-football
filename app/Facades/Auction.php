<?php

namespace App\Facades;

use App\Models\Draft;
use App\Models\LeagueMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection cheatSheet(Draft $draft)
 * @method static Collection marketValues(Draft $draft)
 * @method static Collection projectedValues(Draft $draft)
 * @method static array budget(Draft $draft, LeagueMember $member)
 * @method static array budgetSuggestions(Draft $draft, LeagueMember $member, ?Collection $cheatSheet = null)
 * @method static Collection rosters(Draft $draft)
 * @method static Collection teams(Draft $draft)
 */
class Auction extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Auction';
    }
}
