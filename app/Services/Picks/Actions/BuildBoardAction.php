<?php

namespace App\Services\Picks\Actions;

use App\Models\Draft;
use App\Models\DraftRanking;
use App\Models\LeagueMemberRoster;
use Illuminate\Support\Collection;

/**
 * Every rankable player who can still be taken.
 *
 * Shaped down to what the board actually shows. The whole pool crosses the
 * wire on every pick, so a ranking model with its player attached costs the
 * room about a megabyte a click for eight fields it uses.
 */
class BuildBoardAction
{
    public function run(Draft $draft, float $ppr, bool $superflex): Collection
    {
        $drafted = $draft->picks->pluck('player_id');

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
            ->join('players', 'players.id', '=', 'draft_rankings.player_id')
            ->orderByRank()
            ->get([
                'draft_rankings.id',
                'draft_rankings.player_id',
                'draft_rankings.rank',
                'draft_rankings.tier',
                'draft_rankings.adp',
                'draft_rankings.adv',
                'players.full_name',
                'players.position_id',
                'players.team_id',
            ])
            ->map(fn (DraftRanking $row) => [
                'id'        => $row->id,
                'player_id' => $row->player_id,
                'full_name' => $row->full_name,
                'position'  => $row->position_id,
                'team'      => $row->team_id,
                'rank'      => $row->rank,
                'tier'      => $row->tier,
                'adp'       => $row->adp,
                'adv'       => $row->adv,
            ]);
    }
}
