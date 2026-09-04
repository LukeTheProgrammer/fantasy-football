<?php

namespace App\Services\Picks\Actions;

use App\Models\Draft;
use App\Models\DraftRanking;
use App\Models\LeagueMemberRoster;
use Illuminate\Support\Collection;

/**
 * Every rankable player who can still be taken.
 */
class BuildBoardAction
{
    public function run(Draft $draft, float $ppr, bool $superflex): Collection
    {
        $drafted = $draft->picks()->pluck('player_id');

        // A keeper is owned before the draft opens, so it is off the board for
        // the same reason a pick is: it cannot be taken.
        $kept = LeagueMemberRoster::whereIn('league_member_id', $draft->league->members->pluck('id'))
            ->where('season', $draft->league->season_id)
            ->where('week', 0)
            ->pluck('player_id');

        return DraftRanking::query()
            ->latestRanking($draft->league->season_id, $ppr, $superflex)
            ->forFormat($ppr, $superflex)
            ->where(function ($query) {
                $query->where('rank', '>', 0)
                    ->orWhere('adp', '>', 0)
                    ->orWhere('adv', '>', 0);
            })
            ->whereNotIn('player_id', $drafted->merge($kept)->unique())
            ->with(['player.position', 'player.team'])
            ->orderByRank()
            ->get();
    }
}
