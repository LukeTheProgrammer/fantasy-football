<?php

namespace App\Services\Auction\Helpers;

use App\Models\Draft;
use Illuminate\Support\Collection;

/**
 * The auctions this league ran before the current one, newest first.
 *
 * A league is the same league across seasons by name, since each season is its
 * own row. Only completed auctions with prices on them are of any use, so a
 * season that was never drafted is skipped rather than counted as empty.
 */
class PreviousAuctions
{
    /**
     * How far back the market is read. Older than this the league is a
     * different set of owners with different habits.
     */
    public const SEASONS = 5;

    /**
     * @return Collection<int, Draft>
     */
    public static function for(Draft $draft, int $limit = self::SEASONS): Collection
    {
        return Draft::query()
            ->join('leagues', 'leagues.id', '=', 'drafts.league_id')
            ->where('drafts.draft_type', 'auction')
            ->where('drafts.id', '!=', $draft->id)
            ->where('leagues.name', $draft->league->name)
            ->where('leagues.season_id', '<', $draft->league->season_id)
            ->whereNull('leagues.deleted_at')
            ->orderByDesc('leagues.season_id')
            ->select('drafts.*')
            ->with('league')
            ->limit($limit)
            ->get();
    }
}
