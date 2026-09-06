<?php

namespace App\Services\Picks\Actions;

use App\Enums\Datum;
use App\Facades\Auction;
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
        // filter(): a passed slot is a pick with no player, and it took
        // nobody off the board.
        $drafted = $draft->picks->pluck('player_id')->filter();

        // What the projections say, one step before the auction turns them
        // into dollars: a pick draft spends picks, so the surplus itself is
        // the number the room is choosing on.
        $values = Auction::pointsAboveReplacement($draft);

        // ADP and average value are only ever published on ESPN's board, so
        // they are read from there rather than from the FantasyPros rows the
        // rest of this query is built on, which carry neither.
        $market = $this->marketFigures($draft);

        // Half this roster is kept, so where a player sits on a dynasty board
        // is part of what a pick buys. It is a fourth opinion beside the rank,
        // the market and the projection, never a replacement for them.
        $dynasty = $this->dynastyRanks($draft);

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
                'adp'       => $market->get($row->player_id)['adp'] ?? null,
                'adv'       => $market->get($row->player_id)['adv'] ?? null,
                'points'    => $values->get($row->player_id)['points'] ?? null,
                'par'       => $values->get($row->player_id)['par'] ?? null,
                'dynasty'   => $dynasty->get($row->player_id),
            ]);
    }

    /**
     * Where each player sits on FantasyPros' newest dynasty board.
     *
     * Published in full PPR alone, so a half point league reads it as an
     * ordering and not as a score, the same way ESPN's values are read.
     * Coverage runs a few hundred deep rather than the whole pool, so most of
     * the board has no dynasty rank at all and says so.
     *
     * @return Collection<int, int> Keyed by player id.
     */
    private function dynastyRanks(Draft $draft): Collection
    {
        $season = $draft->league->season_id;

        $latest = DraftRanking::query()
            ->where('season', $season)
            ->where('type', 'dynasty')
            ->max('ranked_at');

        if ($latest === null) {
            return collect();
        }

        return DraftRanking::query()
            ->where('season', $season)
            ->where('type', 'dynasty')
            ->where('ranked_at', $latest)
            ->where('rank', '>', 0)
            ->pluck('rank', 'player_id')
            ->map(fn ($rank) => (int) $rank);
    }

    /**
     * Where the wider market drafts each player and what it pays for him,
     * from ESPN's newest board for this season.
     *
     * Stored exactly as ESPN publishes it, so a superflex league reads a
     * single quarterback league's prices at the position and should know it.
     *
     * @return Collection<int, array<string, float|null>> Keyed by player id.
     */
    private function marketFigures(Draft $draft): Collection
    {
        $season = $draft->league->season_id;

        return DraftRanking::query()
            ->where('season', $season)
            ->fromSource(Datum::SOURCE_ESPN->value)
            ->where('ranked_at', DraftRanking::query()
                ->where('season', $season)
                ->fromSource(Datum::SOURCE_ESPN->value)
                ->max('ranked_at'))
            ->get(['player_id', 'adp', 'adv'])
            ->mapWithKeys(fn (DraftRanking $row) => [
                $row->player_id => [
                    'adp' => $row->adp > 0 ? round((float) $row->adp, 1) : null,
                    'adv' => $row->adv > 0 ? round((float) $row->adv) : null,
                ],
            ]);
    }
}
