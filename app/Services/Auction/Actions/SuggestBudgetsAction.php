<?php

namespace App\Services\Auction\Actions;

use App\Models\Draft;
use App\Models\LeagueMember;
use Illuminate\Support\Collection;

/**
 * Three ways to spend the budget, each built around a different position.
 *
 * A plan buys the best player at its position outright — that is what focusing
 * on a position means — and then splits what is left across the other starting
 * slots in proportion to what the best player left at each is worth, naming the
 * best player each share can actually buy. Money a
 * slot does not use carries forward rather than being lost, so a cheap tight
 * end pays for a better flex. Reading the three side by side is the point —
 * what a quarterback costs is best understood as the running backs it gives up.
 */
class SuggestBudgetsAction
{
    /**
     * The positions a plan can be built around, in the order they are shown.
     */
    public const FOCUSES = ['QB', 'RB', 'WR'];

    /**
     * The least a slot can be planned for, since even the last roster spot
     * costs a dollar at auction.
     */
    private const MINIMUM = 1;

    /**
     * How much more of what is left the focus position's other slots get, once
     * its best player is paid for.
     */
    private const FOCUS_BOOST = 1.3;

    /**
     * Slots that hold whatever is left rather than someone bought on purpose.
     */
    private const RESERVE_SLOTS = ['BE', 'IR', 'OVER'];

    /**
     * Starting slots left unnamed. A kicker and a defence are streamed off
     * waivers for a dollar, so planning who fills them is false precision.
     */
    private const UNNAMED_SLOTS = ['K', 'DST', 'D/ST', 'DEF'];

    /**
     * @return array<int, array<string, mixed>> One plan per focus position.
     */
    public function run(Draft $draft, LeagueMember $member, ?Collection $cheatSheet = null): array
    {
        $slots = $this->slots($draft, $member);

        if ($slots->isEmpty()) {
            return [];
        }

        $board = $this->board($cheatSheet ?? (new BuildCheatSheetAction)->run($draft));

        if ($board->isEmpty()) {
            return [];
        }

        $budget = (int) ($draft->auction_budget ?? 0);

        return collect(self::FOCUSES)
            ->map(fn (string $focus) => $this->plan($focus, $slots, $board, $budget))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * One plan: the budget split across the slots, then the best player each
     * share reaches.
     *
     * @param Collection<int, array> $slots
     * @param Collection<int, array> $board
     *
     * @return array<string, mixed>|null
     */
    private function plan(string $focus, Collection $slots, Collection $board, int $budget): ?array
    {
        $order = $this->order($slots, $focus);
        $bench = $slots->filter(fn (array $slot) => !$slot['is_starter']);

        // The bench is bought with whatever is left at the end of the auction,
        // so it is held back at a dollar a spot rather than planned for.
        $reserve = $bench->count() * self::MINIMUM;

        $claimed = collect();
        $rows = [];
        $carried = 0;

        // Every named slot has to be able to buy somebody, so the cheapest
        // player it could take is costed out before anything is spent. Without
        // that floor a plan can run out of money and leave a starting spot
        // empty, which is not a plan.
        $floors = $this->floors($order, $board);

        // The best player at the focus position is bought outright rather than
        // out of a share: a plan built around a quarterback that cannot reach
        // the best quarterback is not built around him at all.
        $anchor = $this->anchor($order, $board, $focus, $budget - $reserve - array_sum($floors) + ($floors[$this->focusKey($order, $focus)] ?? 0));

        if ($anchor !== null) {
            $claimed->push($anchor['player']['player_id']);

            $rows[$anchor['slot']['key']] = [
                'planned' => $anchor['price'],
                'player'  => [
                    'player_id'   => $anchor['player']['player_id'],
                    'full_name'   => $anchor['player']['full_name'],
                    'position_id' => $anchor['player']['position_id'],
                    'rank'        => $anchor['player']['rank'],
                ],
            ];
        }

        $remaining = collect($order)->reject(fn (array $slot) => isset($rows[$slot['key']]))->values()->all();

        // The floors of the slots still to come are not available to the ones
        // in front of them, so each share is drawn from what is left over.
        $committed = collect($remaining)->sum(fn (array $slot) => $floors[$slot['key']] ?? 0);

        $shares = $this->shares($remaining, $board, $focus, max($budget - $reserve - ($anchor['price'] ?? 0) - $committed, 0), $claimed);

        $spent = ($anchor['price'] ?? 0) + $reserve;

        foreach ($remaining as $position => $slot) {
            $floor = $floors[$slot['key']] ?? 0;
            $share = $shares[$slot['key']] ?? self::MINIMUM;

            // Whatever the shares say, a slot can only spend what is actually
            // left once the slots behind it can still afford their cheapest
            // player. That cap is what keeps a plan inside the budget.
            $later = collect($remaining)
                ->slice($position + 1)
                ->sum(fn (array $next) => max($floors[$next['key']] ?? 0, self::MINIMUM));

            // What this slot did not need is still on the table for the next
            // one, which is how a plan built on averages survives a cheap slot.
            $ceiling = min($floor + $share + $carried, $budget - $spent - $later);

            $player = $this->bestAffordable($board, $slot, $claimed, $ceiling);

            $price = $player === null ? max($share, self::MINIMUM) : max((int) round($player['price']), self::MINIMUM);

            if ($player !== null) {
                $claimed->push($player['player_id']);
            }

            $carried = $ceiling - $price;
            $spent += $price;

            $rows[$slot['key']] = [
                'planned' => $price,
                'player'  => $player === null ? null : [
                    'player_id'   => $player['player_id'],
                    'full_name'   => $player['full_name'],
                    'position_id' => $player['position_id'],
                    'rank'        => $player['rank'],
                ],
            ];
        }

        foreach ($bench as $slot) {
            $rows[$slot['key']] = ['planned' => self::MINIMUM, 'player' => null];
        }

        $planned = collect($rows)->sum('planned');

        return [
            'focus'       => $focus,
            'label'       => $focus . ' first',
            'allocations' => collect($rows)->map(fn (array $row) => $row['planned'])->all(),
            'players'     => collect($rows)->map(fn (array $row) => $row['player'])->all(),
            'planned'     => $planned,
            'unplanned'   => $budget - $planned,
            // What the plan spends on the starting lineup, which is the number
            // that separates the three.
            'starters' => collect($rows)
                ->filter(fn (array $row) => $row['player'] !== null)
                ->sum('planned'),
        ];
    }

    /**
     * What each starting slot is given to spend.
     *
     * A slot's share is what the best player left at it is worth against every
     * other slot, so a thin position is planned for generously without anyone
     * naming a percentage. The focus position is then tilted upwards.
     *
     * @param array<int, array> $order
     * @param Collection<int, array> $board
     *
     * @return array<string, int>
     */
    private function shares(array $order, Collection $board, string $focus, int $spendable, Collection $taken): array
    {
        $starters = collect($order)->filter(fn (array $slot) => $slot['is_starter']);

        if ($starters->isEmpty() || $spendable <= 0) {
            return [];
        }

        $claimed = collect($taken);

        $weights = $starters->mapWithKeys(function (array $slot) use ($board, $focus, $claimed) {
            // Each slot is weighed against a different player, so two running
            // back slots are not both priced as though they get the best one.
            $best = $this->bestAffordable($board, $slot, $claimed, PHP_INT_MAX);

            if ($best !== null) {
                $claimed->push($best['player_id']);
            }

            $weight = max((float) ($best['price'] ?? self::MINIMUM), self::MINIMUM);

            return [$slot['key'] => $slot['slot'] === $focus ? $weight * self::FOCUS_BOOST : $weight];
        });

        $total = $weights->sum();

        $shares = $weights
            ->map(fn (float $weight) => max((int) round($spendable * $weight / $total), self::MINIMUM))
            ->all();

        return $this->settle($shares, $spendable);
    }

    /**
     * Rounding each share separately leaves the plan a dollar or two off the
     * budget, so the difference is put on the largest share, which is the one
     * that can carry it either way.
     *
     * @param array<string, int> $shares
     *
     * @return array<string, int>
     */
    private function settle(array $shares, int $spendable): array
    {
        $difference = $spendable - array_sum($shares);

        if ($difference === 0 || $shares === []) {
            return $shares;
        }

        $largest = array_search(max($shares), $shares, true);

        $shares[$largest] = max($shares[$largest] + $difference, self::MINIMUM);

        return $shares;
    }

    /**
     * The cheapest player each named starting slot could fall back on, keyed by
     * slot. A slot nobody is planned for — a kicker, a defence — has no floor.
     *
     * @param array<int, array> $order
     * @param Collection<int, array> $board
     *
     * @return array<string, int>
     */
    private function floors(array $order, Collection $board): array
    {
        $claimed = collect();
        $floors = [];

        foreach ($order as $slot) {
            if (!$this->isNamed($slot)) {
                continue;
            }

            $cheapest = $board
                ->filter(fn (array $player) => !$claimed->contains($player['player_id'])
                    && in_array($player['position_id'], $this->eligible($slot['slot'])))
                ->sortBy('price')
                ->first();

            if ($cheapest === null) {
                continue;
            }

            $claimed->push($cheapest['player_id']);

            $floors[$slot['key']] = max((int) round($cheapest['price']), self::MINIMUM);
        }

        return $floors;
    }

    /**
     * Whether a slot is one the plan names a player for.
     *
     * @param array<string, mixed> $slot
     */
    private function isNamed(array $slot): bool
    {
        return $slot['is_starter']
            && !in_array($slot['slot'], self::RESERVE_SLOTS)
            && !in_array($slot['slot'], self::UNNAMED_SLOTS);
    }

    /**
     * The key of the slot the focus position is anchored in.
     *
     * @param array<int, array> $order
     */
    private function focusKey(array $order, string $focus): ?string
    {
        return collect($order)->first(fn (array $slot) => $slot['is_starter'] && $slot['slot'] === $focus)['key'] ?? null;
    }

    /**
     * The best player at the focus position, and the slot he goes in.
     *
     * A plan that cannot afford him without starving the rest of the roster
     * simply has no anchor, and falls back to shares like any other slot.
     *
     * @param array<int, array> $order
     * @param Collection<int, array> $board
     *
     * @return array{slot: array, player: array, price: int}|null
     */
    private function anchor(array $order, Collection $board, string $focus, int $ceiling): ?array
    {
        $slot = collect($order)->first(fn (array $slot) => $slot['is_starter'] && $slot['slot'] === $focus);

        if ($slot === null || $ceiling < self::MINIMUM) {
            return null;
        }

        $player = $this->bestAffordable($board, $slot, collect(), $ceiling);

        if ($player === null) {
            return null;
        }

        return [
            'slot'   => $slot,
            'player' => $player,
            'price'  => max((int) round($player['price']), self::MINIMUM),
        ];
    }

    /**
     * The slots in the order they are bought: the focus position first, then
     * the rest as the league lists them, then the bench.
     *
     * @param Collection<int, array> $slots
     *
     * @return array<int, array>
     */
    private function order(Collection $slots, string $focus): array
    {
        $starters = $slots->filter(fn (array $slot) => $slot['is_starter']);

        return $starters
            ->filter(fn (array $slot) => $slot['slot'] === $focus)
            ->concat($starters->reject(fn (array $slot) => $slot['slot'] === $focus))
            ->concat($slots->reject(fn (array $slot) => $slot['is_starter']))
            ->values()
            ->all();
    }

    /**
     * The best player left that this slot can take and the money can reach.
     *
     * @param Collection<int, array> $board
     * @param array<string, mixed> $slot
     * @param Collection<int, int> $claimed
     *
     * @return array<string, mixed>|null
     */
    private function bestAffordable(Collection $board, array $slot, Collection $claimed, int $ceiling): ?array
    {
        if (!$this->isNamed($slot) || $ceiling < self::MINIMUM) {
            return null;
        }

        $positions = $this->eligible($slot['slot']);

        return $board
            ->first(fn (array $player) => !$claimed->contains($player['player_id'])
                && in_array($player['position_id'], $positions)
                && $player['price'] <= $ceiling);
    }

    /**
     * The positions a slot accepts. Flex slots name theirs joined by
     * underscores, the way the league stores them.
     *
     * @return array<int, string>
     */
    private function eligible(string $slot): array
    {
        return str_contains($slot, '_') ? explode('_', $slot) : [$slot];
    }

    /**
     * This member's roster slots, keyed the same way the budget plan is so a
     * suggestion can be applied to it directly.
     *
     * @return Collection<int, array>
     */
    private function slots(Draft $draft, LeagueMember $member): Collection
    {
        $slots = collect((new SlotRostersAction)->run($draft)->get($member->id) ?? [])
            ->reject(fn (array $slot) => $slot['slot'] === 'OVER');

        $totals = $slots->countBy('slot');
        $counts = [];

        return $slots->map(function (array $slot) use (&$counts, $totals) {
            $number = ($counts[$slot['slot']] = ($counts[$slot['slot']] ?? 0) + 1);

            return [
                ...$slot,
                'key' => $totals[$slot['slot']] > 1 ? $slot['label'] . $number : $slot['label'],
            ];
        })->values();
    }

    /**
     * The draftable players, best first, each with the price the plan should
     * expect to pay.
     *
     * @param Collection<int, array> $cheatSheet
     *
     * @return Collection<int, array>
     */
    private function board(Collection $cheatSheet): Collection
    {
        return $cheatSheet
            ->filter(fn (array $row) => $row['drafted_by'] === null && $row['position_id'] !== null)
            ->map(fn (array $row) => [
                'player_id'   => $row['player_id'],
                'full_name'   => $row['full_name'],
                'position_id' => $row['position_id'],
                'rank'        => $row['rank'],
                // The league's own curve is the price this plan is spent in;
                // the other estimates stand in when a rank has no history.
                'price' => (float) ($row['market_value'] ?? $row['projected_value'] ?? $row['adv'] ?? self::MINIMUM),
            ])
            ->sortBy('rank')
            ->values();
    }
}
