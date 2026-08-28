<?php

namespace App\Services\Auction\Helpers;

use App\Models\NflGame;
use Illuminate\Support\Collection;

/**
 * The week each NFL team does not play.
 *
 * Byes are held as their own schedule rows rather than inferred from the weeks
 * a team is missing from, so one query answers the whole league.
 */
class ByeWeeks
{
    /**
     * @return Collection<string, int> Keyed by NFL team id.
     */
    public static function for(int $season): Collection
    {
        return NflGame::query()
            ->where('season', $season)
            ->where('is_bye', true)
            ->pluck('week', 'home_team_id')
            ->map(fn ($week) => (int) $week);
    }
}
