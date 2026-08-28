<?php

namespace App\Services\Auction\Actions;

use App\Enums\Datum;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\DraftRanking;
use App\Services\Auction\Helpers\ByeWeeks;
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

        $projections = (new CalculateProjectedValuesAction)->projectedPoints($draft);

        $marketValues = (new CalculateMarketValuesAction)->run($draft);
        $projectedValues = (new CalculateProjectedValuesAction)->run($draft, $projections);
        $previousPrices = $this->previousPrices($draft);
        $projectedPoints = $projections->mapWithKeys(fn ($player) => [
            $player['player_id'] => round($player['points'], 1),
        ]);
        $averageValues = $this->averageDraftValues($draft);
        $drafted = $draft->picks->keyBy('player_id');
        $byeWeeks = ByeWeeks::for($league->season);

        return $rankings->map(function (DraftRanking $ranking) use (
            $marketValues,
            $projectedValues,
            $previousPrices,
            $projectedPoints,
            $averageValues,
            $drafted,
            $byeWeeks,
            $league
        ) {
            $player = $ranking->player;
            $pick = $drafted->get($ranking->player_id);

            return [
                'player_id'        => $ranking->player_id,
                'full_name'        => $player?->full_name,
                'position_id'      => $player?->position_id,
                'team_id'          => $player?->team_id,
                'bye_week'         => $player?->team_id ? $byeWeeks->get($player->team_id) : null,
                'headshot'         => $player?->headshot,
                'rank'             => $ranking->rank,
                'tier'             => $ranking->tier,
                'projected_points' => $projectedPoints->get($ranking->player_id),
                'market_value'     => $marketValues->get($ranking->rank),
                'projected_value'  => $projectedValues->get($ranking->player_id),
                'previous_price'   => $previousPrices->get($ranking->player_id),
                'adv'              => $averageValues->get($ranking->player_id),
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
            ->latestRanking($season, $format[0], $format[1])
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
     * What the wider market pays for each player, from ESPN's newest board for
     * this season.
     *
     * Stored exactly as the source publishes it, so a superflex league reads a
     * single quarterback league's prices at the position and should know it.
     *
     * @return Collection<int, float> Keyed by player id.
     */
    private function averageDraftValues(Draft $draft): Collection
    {
        $season = $draft->league->season;

        return DraftRanking::query()
            ->where('season', $season)
            ->fromSource(Datum::SOURCE_ESPN->value)
            ->where('ranked_at', DraftRanking::query()
                ->where('season', $season)
                ->fromSource(Datum::SOURCE_ESPN->value)
                ->max('ranked_at'))
            ->where('adv', '>', 0)
            ->pluck('adv', 'player_id')
            ->map(fn ($value) => round((float) $value));
    }
}
