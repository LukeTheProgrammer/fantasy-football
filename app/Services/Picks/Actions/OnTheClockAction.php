<?php

namespace App\Services\Picks\Actions;

use App\Models\Draft;
use App\Models\LeagueMember;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Reads the draft order to say whose turn it is and what comes after.
 */
class OnTheClockAction
{
    /**
     * @return array<string, mixed>
     */
    public function run(Draft $draft, int $upcoming = 8): array
    {
        $order = $draft->draft_order ?? [];

        // Picks already made are the position in the order, so a pick undone
        // puts the same slot back on the clock.
        $made = $draft->picks()->count();

        $members = $draft->league->members->keyBy('external_id');

        $slots = [];

        foreach (array_slice($order, $made, $upcoming + 1, true) as $index => $externalId) {
            $member = $members->get($externalId);

            $slots[] = $this->slot($index, $member, $draft);
        }

        return [
            'made'      => $made,
            'total'     => count($order),
            'remaining' => max(count($order) - $made, 0),
            'current'   => Arr::first($slots),
            'upcoming'  => array_slice($slots, 1),
            'round'     => $this->round($draft, $order, $members, $made),
        ];
    }

    /**
     * Every slot in the round the clock is on, whether it has been used yet
     * or not, so the board can show the round as a whole.
     *
     * @param array<int, string> $order
     *
     * @return array<int, array<string, mixed>>
     */
    private function round(Draft $draft, array $order, Collection $members, int $made): array
    {
        if (empty($order)) {
            return [];
        }

        $teams = max($draft->league->team_count, 1);

        // The clock sits past the end once the draft is done, and the last
        // round is the one worth showing then.
        $index = min($made, count($order) - 1);
        $start = intdiv($index, $teams) * $teams;

        $slots = [];

        foreach (array_slice($order, $start, $teams, true) as $slotIndex => $externalId) {
            $slots[] = [
                ...$this->slot($slotIndex, $members->get($externalId), $draft),
                'is_made'    => $slotIndex < $made,
                'is_current' => $slotIndex === $made,
            ];
        }

        return $slots;
    }

    /**
     * @return array<string, mixed>
     */
    private function slot(int $index, ?LeagueMember $member, Draft $draft): array
    {
        $teams = max($draft->league->team_count, 1);

        return [
            'overall_pick_number' => $index + 1,
            // A traded pick keeps the slot it was traded for, so the round is
            // the slot's place in the order rather than a count per team.
            'round'            => intdiv($index, $teams) + 1,
            'pick_number'      => ($index % $teams) + 1,
            'league_member_id' => $member?->id,
            'external_id'      => $member?->external_id,
            'team_name'        => $member?->team_name,
            'owner_name'       => $member?->owner_name,
        ];
    }
}
