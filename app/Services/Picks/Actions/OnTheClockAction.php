<?php

namespace App\Services\Picks\Actions;

use App\Models\Draft;
use App\Models\DraftPick;
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
            // Every round, so the board can be paged back and forth without
            // another trip to the server.
            'rounds'        => $this->rounds($draft, $order, $members, $made),
            'current_round' => $this->currentRound($draft, $order, $made),
        ];
    }

    /**
     * The draft laid out round by round, whether a slot has been used yet or
     * not, so the board can show any round and not only the live one.
     *
     * @param array<int, string> $order
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function rounds(Draft $draft, array $order, Collection $members, int $made): array
    {
        if (empty($order)) {
            return [];
        }

        $teams = max($draft->league->team_count, 1);

        // A made pick is shown as the player who was taken, so the board reads
        // as a record of the round rather than only a running order.
        $picks = $draft->picks->keyBy('overall_pick_number');

        $rounds = [];

        foreach (array_chunk($order, $teams, true) as $chunk) {
            $slots = [];

            foreach ($chunk as $slotIndex => $externalId) {
                $slots[] = [
                    ...$this->slot($slotIndex, $members->get($externalId), $draft),
                    'is_made'    => $slotIndex < $made,
                    'is_current' => $slotIndex === $made,
                    'player'     => $this->player($picks->get($slotIndex + 1)),
                ];
            }

            $rounds[] = $slots;
        }

        return $rounds;
    }

    /**
     * The round the clock is on, which is the last round once it is over.
     *
     * @param array<int, string> $order
     */
    private function currentRound(Draft $draft, array $order, int $made): int
    {
        if (empty($order)) {
            return 1;
        }

        $teams = max($draft->league->team_count, 1);

        return intdiv(min($made, count($order) - 1), $teams) + 1;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function player(?DraftPick $pick): ?array
    {
        if (!$pick instanceof DraftPick) {
            return null;
        }

        return [
            'player_id' => $pick->player_id,
            'pick_id'   => $pick->id,
            'full_name' => $pick->player?->full_name,
            'position'  => $pick->player?->position_id,
            'team'      => $pick->player?->team_id,
            'headshot'  => $pick->player?->headshot,
        ];
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
