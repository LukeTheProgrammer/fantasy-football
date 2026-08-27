<?php

namespace App\Services\Auction\Actions;

use App\Models\Draft;
use App\Models\DraftBudget;
use App\Models\LeagueMember;

/**
 * A team's spending plan next to what it has actually spent.
 *
 * One row per roster spot, bench included, keyed by the slot and its number
 * within that slot ("QB1", "RB2", "BE3"). Keying by name rather than by the
 * template's index means a plan survives the league adding a slot ahead of the
 * ones already planned for. The plan itself is never adjusted here: an
 * overspend shows as a difference and what to do about it stays a decision
 * rather than a calculation.
 */
class BuildBudgetAction
{
    /**
     * Slots that exist only to hold a pick with nowhere else to go, and so are
     * never planned for.
     */
    private const UNPLANNED_SLOTS = ['OVER'];

    public function run(Draft $draft, LeagueMember $member): array
    {
        $rosters = (new SlotRostersAction)->run($draft);
        $slots = collect($rosters->get($member->id) ?? []);

        $plan = $this->plan($draft, $member);

        $counts = [];

        $rows = $slots
            ->reject(fn (array $slot) => in_array($slot['slot'], self::UNPLANNED_SLOTS))
            ->map(function (array $slot) use (&$counts, $plan) {
                $number = ($counts[$slot['slot']] = ($counts[$slot['slot']] ?? 0) + 1);
                $key = $slot['label'] . $number;

                return $this->row(
                    key: $key,
                    label: $key,
                    planned: $plan[$key] ?? null,
                    actual: $slot['player']['amount'] ?? null,
                    filledBy: $slot['player']['full_name'] ?? null,
                );
            })
            ->values();

        $budget = (int) ($draft->auction_budget ?? 0);
        $planned = $rows->sum('planned');
        $actual = $rows->sum('actual');

        return [
            'rows'    => $rows->all(),
            'budget'  => $budget,
            'planned' => $planned,
            // What the plan has not accounted for, which is the number that
            // says whether the plan is finished.
            'unplanned' => $budget - $planned,
            'actual'    => $actual,
            'remaining' => $budget - $actual,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function row(string $key, string $label, ?int $planned, ?int $actual, ?string $filledBy): array
    {
        return [
            'key'     => $key,
            'label'   => $label,
            'planned' => $planned,
            'actual'  => $actual,
            // Positive means the slot came in under what was planned for it.
            'difference' => $planned !== null && $actual !== null ? $planned - $actual : null,
            'filled_by'  => $filledBy,
        ];
    }

    /**
     * The saved plan, as whole dollars keyed by slot name.
     *
     * @return array<string, int>
     */
    private function plan(Draft $draft, LeagueMember $member): array
    {
        $budget = DraftBudget::query()
            ->where('draft_id', $draft->id)
            ->where('league_member_id', $member->id)
            ->first();

        return collect($budget?->allocations ?? [])
            ->map(fn ($amount) => (int) $amount)
            ->all();
    }
}
