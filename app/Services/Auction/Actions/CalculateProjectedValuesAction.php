<?php

namespace App\Services\Auction\Actions;

use App\Enums\Datum;
use App\Models\Draft;
use App\Models\PlayerProjection;
use Illuminate\Support\Collection;

/**
 * Prices players against the budget using points above replacement.
 *
 * A player is only worth real money for the points he adds over the best
 * player the league could roster for a dollar. Every roster spot reserves that
 * dollar first, and what is left of the league's money is split across the
 * surplus points in the draftable pool.
 *
 * Projections are weekly rather than season long, which does not matter here:
 * surplus is normalised against the same budget either way, so a constant
 * scale factor cancels out.
 */
class CalculateProjectedValuesAction
{
    /**
     * The minimum bid a roster spot costs.
     */
    public const MINIMUM_BID = 1.0;

    /**
     * Positions that may fill the roster's flex spot, priced against a single
     * shared replacement level.
     */
    public const FLEX_POSITIONS = ['RB', 'WR', 'TE'];

    /**
     * @param Collection|null $points Projected points already fetched by the
     *                                caller, in the shape projectedPoints()
     *                                returns, so the sheet and this action
     *                                read the projections table once.
     *
     * @return Collection<int, float> Dollar value keyed by player id.
     */
    public function run(Draft $draft, ?Collection $points = null): Collection
    {
        $league = $draft->league;
        $teamCount = max(1, $league->members->count());
        $rosterSize = (int) ($league->settings?->roster_size ?? 0);

        $points ??= $this->projectedPoints($draft);

        if ($points->isEmpty()) {
            return collect();
        }

        $replacement = $this->replacementLevels($points, $league->settings?->roster_positions ?? [], $teamCount);

        $surplus = $points
            ->map(fn ($player) => [
                'player_id' => $player['player_id'],
                'surplus'   => max(0.0, $player['points'] - ($replacement[$this->pricingGroup($player['position'])] ?? 0.0)),
            ])
            ->filter(fn ($player) => $player['surplus'] > 0);

        $totalSurplus = $surplus->sum('surplus');

        if ($totalSurplus <= 0) {
            return collect();
        }

        // Every roster spot in the league costs at least the minimum bid, so
        // only what is left over is bid up.
        $totalBudget = $teamCount * (float) ($draft->auction_budget ?? 0);
        $reserved = $teamCount * $rosterSize * self::MINIMUM_BID;
        $biddable = max(0.0, $totalBudget - $reserved);

        return $surplus->mapWithKeys(fn ($player) => [
            $player['player_id'] => round(
                self::MINIMUM_BID + ($player['surplus'] / $totalSurplus) * $biddable,
                0
            ),
        ]);
    }

    /**
     * Projected points per player, in the scoring format this league uses,
     * falling back to standard scoring for positions the format does not
     * publish separately.
     */
    public function projectedPoints(Draft $draft): Collection
    {
        $league = $draft->league;
        $ppr = $league->settings?->pprValue() ?? 0.0;

        $projections = PlayerProjection::query()
            ->forSeason($league->season)
            ->fromSource(Datum::SOURCE_FANTASY_PROS)
            ->where('superflex', false)
            ->where('projected_points', '>', 0)
            ->with('player:id,position_id')
            ->get();

        return $projections
            ->groupBy('player_id')
            ->map(function (Collection $rows) use ($ppr) {
                $row = $rows->firstWhere('ppr', $ppr) ?? $rows->sortBy('ppr')->first();

                return [
                    'player_id' => $row->player_id,
                    'position'  => $row->player?->position_id,
                    'points'    => (float) $row->projected_points,
                ];
            })
            ->filter(fn ($player) => !empty($player['position']))
            ->values();
    }

    /**
     * The points scored by the last starter at each position, which is what a
     * replacement level player is worth.
     *
     * @return array<string, float>
     */
    private function replacementLevels(Collection $points, array $rosterPositions, int $teamCount): array
    {
        $starters = $this->startersPerTeam($rosterPositions);
        $levels = [];

        // Flex eligible positions compete for the same spots, so they share one
        // replacement level drawn from the combined pool.
        $flexStarters = collect(self::FLEX_POSITIONS)->sum(fn ($position) => $starters[$position] ?? 0)
            + ($starters['FLEX'] ?? 0);

        $levels['FLEX'] = $this->pointsAtDepth(
            $points->filter(fn ($player) => in_array($player['position'], self::FLEX_POSITIONS)),
            $flexStarters * $teamCount
        );

        foreach ($starters as $position => $count) {
            if ($position === 'FLEX' || in_array($position, self::FLEX_POSITIONS)) {
                continue;
            }

            $levels[$position] = $this->pointsAtDepth(
                $points->filter(fn ($player) => $player['position'] === $position),
                $count * $teamCount
            );
        }

        return $levels;
    }

    /**
     * Points scored by the player at a given depth in a pool.
     */
    private function pointsAtDepth(Collection $players, int $depth): float
    {
        if ($depth < 1 || $players->isEmpty()) {
            return 0.0;
        }

        $sorted = $players->sortByDesc('points')->values();

        return (float) ($sorted->get($depth - 1)['points'] ?? $sorted->last()['points']);
    }

    /**
     * Starting spots per team, by position, read from the roster template.
     *
     * @return array<string, int>
     */
    private function startersPerTeam(array $rosterPositions): array
    {
        $starters = [];

        foreach ($rosterPositions as $slot) {
            // Bench and injured reserve spots are not started, so they do not
            // move the replacement level.
            if (in_array($slot, ['BE', 'IR'])) {
                continue;
            }

            $key = str_contains($slot, '_') ? 'FLEX' : $slot;

            $starters[$key] = ($starters[$key] ?? 0) + 1;
        }

        return $starters;
    }

    /**
     * The replacement level a position is priced against.
     */
    private function pricingGroup(?string $position): string
    {
        return in_array($position, self::FLEX_POSITIONS) ? 'FLEX' : (string) $position;
    }
}
