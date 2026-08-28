<?php

namespace App\Services\Auction\Actions;

use App\Models\Draft;
use Illuminate\Support\Collection;

/**
 * The state of the room rather than of any one player: whether the auction is
 * running hot or cold, and which positions still have money chasing them.
 *
 * Inflation is the number that moves a plan mid draft. Every value estimate on
 * the board is priced against a fixed budget, so once the league has overpaid
 * for the players already gone, the players still on the board must go for less
 * than they are marked at — or the money runs out first.
 */
class SummariseMarketAction
{
    /**
     * Positions the board is scarce in, in the order they are shown.
     */
    public const POSITIONS = ['QB', 'RB', 'WR', 'TE', 'K', 'DST'];

    /**
     * Slots that hold a player without starting him, so they are not a need.
     */
    public const RESERVE_SLOTS = ['BE', 'IR'];

    /**
     * @param Collection<int, array> $cheatSheet Rows from BuildCheatSheetAction.
     *
     * @return array<string, mixed>
     */
    public function run(Draft $draft, Collection $cheatSheet): array
    {
        $teams = (new SummariseTeamsAction)->run($draft);
        $needs = $this->openStarterSlots($draft);

        [$drafted, $available] = $cheatSheet->partition(fn (array $row) => $row['drafted_by'] !== null);

        $spent = (int) $drafted->sum('drafted_for');
        $expected = (int) round($drafted->sum(fn (array $row) => $this->value($row) ?? 0));

        $moneyLeft = (int) $teams->sum('remaining');
        $spotsLeft = (int) $teams->sum('open_spots');

        return [
            'spent'    => $spent,
            'expected' => $expected,
            'picks'    => $drafted->count(),
            // Nothing is inflated before anything has sold, and a league that
            // has somehow spent nothing on players worth nothing is not either.
            'inflation'  => $expected > 0 ? round((($spent - $expected) / $expected) * 100, 1) : null,
            'money_left' => $moneyLeft,
            'spots_left' => $spotsLeft,
            // What the board still holds, counted only as deep as the league
            // can actually buy: value beyond the last roster spot is not value.
            'value_left' => (int) round(
                $available->sortBy('rank')->take($spotsLeft)->sum(fn (array $row) => $this->value($row) ?? 0)
            ),
            'positions' => $this->positions($drafted, $available, $teams, $needs),
        ];
    }

    /**
     * One row per position: what is left on the board and who still has to buy
     * it.
     *
     * @param Collection<int, array> $drafted
     * @param Collection<int, array> $available
     * @param Collection<int, array> $teams
     * @param Collection<int, array<string, int>> $needs Open starter slots per position, keyed by team.
     *
     * @return array<int, array<string, mixed>>
     */
    private function positions(Collection $drafted, Collection $available, Collection $teams, Collection $needs): array
    {
        return collect(self::POSITIONS)
            ->map(function (string $position) use ($drafted, $available, $teams, $needs) {
                $left = $available->where('position_id', $position)->sortBy('rank')->values();

                // The tier the next player off the board sits in, and how many
                // more share it. Once that count reaches zero the price of the
                // position steps up, which is the moment to stop waiting.
                $topTier = $left->first()['tier'] ?? null;

                $buyers = $teams->filter(fn (array $team) => ($needs->get($team['id'])[$position] ?? 0) > 0
                    && $team['remaining'] > 0);

                return [
                    'position'      => $position,
                    'drafted'       => $drafted->where('position_id', $position)->count(),
                    'available'     => $left->count(),
                    'top_tier'      => $topTier,
                    'top_tier_left' => $topTier === null
                        ? 0
                        : $left->where('tier', $topTier)->count(),
                    'slots_open'    => (int) $needs->sum(fn (array $team) => $team[$position] ?? 0),
                    'teams_needing' => $buyers->count(),
                    'money_chasing' => (int) $buyers->sum('remaining'),
                ];
            })
            ->all();
    }

    /**
     * Unfilled starting slots per position for every team, counting a flex once
     * for each position it accepts — a flex is a need at all of them until it
     * is filled by one.
     *
     * @return Collection<int, array<string, int>> Keyed by league member id.
     */
    private function openStarterSlots(Draft $draft): Collection
    {
        return (new SlotRostersAction)->run($draft)
            ->map(function (array $slots) {
                $open = array_fill_keys(self::POSITIONS, 0);

                foreach ($slots as $slot) {
                    if ($slot['player'] !== null || in_array($slot['slot'], self::RESERVE_SLOTS)) {
                        continue;
                    }

                    foreach ($this->accepts($slot['slot']) as $position) {
                        $open[$position]++;
                    }
                }

                return $open;
            });
    }

    /**
     * The positions a slot can be filled by. Flex slots name them joined by
     * underscores; every other slot names exactly one.
     *
     * @return array<int, string>
     */
    private function accepts(string $slot): array
    {
        $positions = str_contains($slot, '_') ? explode('_', $slot) : [$slot];

        return array_values(array_intersect($positions, self::POSITIONS));
    }

    /**
     * What a player was marked at before he sold. The league's own price curve
     * is the honest yardstick for inflation, since it is the one built from
     * what this room has actually paid.
     */
    private function value(array $row): ?float
    {
        return $row['market_value'] ?? $row['projected_value'] ?? $row['adv'] ?? null;
    }
}
