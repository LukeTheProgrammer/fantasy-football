<?php

namespace App\Services\Auction\Actions;

use App\Models\Draft;
use App\Models\DraftBudget;
use App\Models\LeagueMember;
use Illuminate\Support\Collection;

/**
 * A team's spending plan next to what it has actually spent.
 *
 * One row per starting slot, plus a single row pooling the bench, because a
 * plan for nine individual bench spots is bookkeeping rather than planning.
 * The plan itself is never adjusted here: an overspend shows as a difference
 * and what to do about it stays a decision rather than a calculation.
 */
class BuildBudgetAction
{
    public function run(Draft $draft, LeagueMember $member): array
    {
        $rosters = (new SlotRostersAction)->run($draft);
        $slots = collect($rosters->get($member->id) ?? []);

        $plan = $this->plan($draft, $member);

        $rows = $slots
            ->filter(fn (array $slot) => $slot['is_starter'])
            ->map(fn (array $slot) => $this->row(
                key: (string) $slot['index'],
                label: $slot['label'],
                planned: $plan[(string) $slot['index']] ?? null,
                actual: $slot['player']['amount'] ?? null,
                filledBy: $slot['player']['full_name'] ?? null,
            ))
            ->values();

        $rows->push($this->benchRow($slots, $plan));

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
     * The bench planned as one pool, against everything actually spent off the
     * starting lineup.
     *
     * @param Collection<int, array> $slots
     * @param array<string, int> $plan
     */
    private function benchRow(Collection $slots, array $plan): array
    {
        $bench = $slots->reject(fn (array $slot) => $slot['is_starter']);

        $spent = $bench->sum(fn (array $slot) => $slot['player']['amount'] ?? 0);
        $filled = $bench->filter(fn (array $slot) => $slot['player'] !== null)->count();

        return $this->row(
            key: DraftBudget::BENCH_KEY,
            label: 'Bench (' . $bench->count() . ')',
            planned: $plan[DraftBudget::BENCH_KEY] ?? null,
            actual: $spent > 0 ? $spent : null,
            filledBy: $filled > 0 ? $filled . ' filled' : null,
        );
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
     * The saved plan, as whole dollars keyed by slot.
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
