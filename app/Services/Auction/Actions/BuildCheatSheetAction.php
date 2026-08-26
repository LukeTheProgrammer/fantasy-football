<?php

namespace App\Services\Auction\Actions;

use App\Enums\Datum;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\DraftRanking;
use App\Models\PlayerProjection;
use App\Services\Auction\Helpers\PreviousAuction;
use Illuminate\Support\Collection;

/**
 * One row per rankable player, carrying everything the sheet shows: where he
 * ranks, what he projects, both value estimates, and what this league paid for
 * him last time.
 */
class BuildCheatSheetAction
{
    public function run(Draft $draft): Collection
    {
        $league = $draft->league;

        $rankings = $this->rankings($draft);

        if ($rankings->isEmpty()) {
            return collect();
        }

        $marketValues = (new CalculateMarketValuesAction)->run($draft);
        $projectedValues = (new CalculateProjectedValuesAction)->run($draft);
        $previousPrices = $this->previousPrices($draft);
        $projectedPoints = $this->projectedPoints($draft);
        $drafted = $draft->picks->keyBy('player_id');

        return $rankings->map(function (DraftRanking $ranking) use (
            $marketValues,
            $projectedValues,
            $previousPrices,
            $projectedPoints,
            $drafted,
            $league
        ) {
            $player = $ranking->player;
            $pick = $drafted->get($ranking->player_id);

            return [
                'player_id'        => $ranking->player_id,
                'full_name'        => $player?->full_name,
                'position_id'      => $player?->position_id,
                'team_id'          => $player?->team_id,
                'headshot'         => $player?->headshot,
                'rank'             => $ranking->rank,
                'tier'             => $ranking->tier,
                'projected_points' => $projectedPoints->get($ranking->player_id),
                'market_value'     => $marketValues->get($ranking->rank),
                'projected_value'  => $projectedValues->get($ranking->player_id),
                'previous_price'   => $previousPrices->get($ranking->player_id),
                'drafted_by'       => $pick?->league_member_id,
                'drafted_for'      => $pick ? (int) $pick->amount : null,
                'pick_id'          => $pick?->id,
                'season'           => $league->season,
            ];
        })->values();
    }

    /**
     * The newest rankings in the format this league is scored under.
     */
    private function rankings(Draft $draft): Collection
    {
        $league = $draft->league;

        $season = $league->season;
        $ppr = $league->settings?->pprValue() ?? 0.0;
        $superflex = (bool) $league->settings?->two_qb;

        $available = DraftRanking::query()
            ->availableFormats($season)
            ->get()
            ->map(fn (DraftRanking $ranking) => [(float) $ranking->ppr, (bool) $ranking->superflex]);

        // Superflex reshapes a draft board more than the reception value does,
        // so it is matched before the scoring format.
        $format = $available->first(fn ($candidate) => $candidate === [$ppr, $superflex])
            ?? $available->first(fn ($candidate) => $superflex && $candidate[1] === true)
            ?? $available->first(fn ($candidate) => $candidate[0] === $ppr && $candidate[1] === false)
            ?? $available->first();

        if (!$format) {
            return collect();
        }

        return DraftRanking::query()
            ->latestRanking($season)
            ->forFormat($format[0], $format[1])
            ->where('rank', '>', 0)
            ->with(['player:id,full_name,position_id,team_id,headshot'])
            ->orderBy('rank')
            ->get();
    }

    /**
     * What this league paid for each player in its last auction.
     *
     * @return Collection<int, int> Keyed by player id.
     */
    private function previousPrices(Draft $draft): Collection
    {
        $previous = PreviousAuction::for($draft);

        if (!$previous instanceof Draft) {
            return collect();
        }

        return $previous->picks()
            ->whereNotNull('player_id')
            ->get()
            ->mapWithKeys(fn (DraftPick $pick) => [$pick->player_id => (int) $pick->amount]);
    }

    /**
     * Projected points per player in this league's scoring format.
     *
     * @return Collection<int, float> Keyed by player id.
     */
    private function projectedPoints(Draft $draft): Collection
    {
        $league = $draft->league;
        $ppr = $league->settings?->pprValue() ?? 0.0;

        return PlayerProjection::query()
            ->forSeason($league->season)
            ->fromSource(Datum::SOURCE_FANTASY_PROS)
            ->where('superflex', false)
            ->where('projected_points', '>', 0)
            ->get()
            ->groupBy('player_id')
            ->map(function (Collection $rows) use ($ppr) {
                $row = $rows->firstWhere('ppr', $ppr) ?? $rows->sortBy('ppr')->first();

                return round((float) $row->projected_points, 1);
            });
    }
}
