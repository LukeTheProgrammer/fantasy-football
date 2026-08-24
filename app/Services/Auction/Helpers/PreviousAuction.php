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
        return Draft::query()
            ->join('leagues', 'leagues.id', '=', 'drafts.league_id')
            ->where('drafts.draft_type', 'auction')
            ->where('drafts.id', '!=', $draft->id)
            ->where('leagues.name', $draft->league->name)
            ->where('leagues.season', '<', $draft->league->season)
            ->whereNull('leagues.deleted_at')
            ->orderByDesc('leagues.season')
            ->select('drafts.*')
            ->first();
    }
}
