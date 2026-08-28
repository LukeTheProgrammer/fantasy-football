<?php

namespace App\Services\Auction\Helpers;

use App\Models\Draft;

/**
 * Finds the auction this league ran before the current one, which is where
 * both the price curve and each player's last price come from.
 */
class PreviousAuction
{
    public static function for(Draft $draft): ?Draft
    {
        return PreviousAuctions::for($draft, 1)->first();
    }
}
