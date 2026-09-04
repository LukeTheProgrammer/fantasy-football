<?php

namespace App\Services\Auction\Actions;

use App\Facades\Roster;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\LeagueMember;
use Illuminate\Support\Collection;

/**
 * Places each team's picks into the roster the league is configured for.
 *
 * Picks are slotted most expensive first in an auction and in pick order in a
 * snake, since that is what says who was taken to start. The placing itself is
 * the same in any room, so it is done through the roster facade.
 */
class SlotRostersAction
{
    /**
     * @return Collection<int, array> Roster slots keyed by league member id.
     */
    public function run(Draft $draft): Collection
    {
        $template = $draft->league->settings?->roster_positions ?? [];

        // In an auction the price is the clearest signal of who was bought to
        // start; in a snake it is the order they were taken in.
        $picks = ($draft->draft_type === 'auction'
            ? $draft->picks->sortByDesc('amount')
            : $draft->picks->sortBy('pick_number'))
            ->groupBy('league_member_id');

        return $draft->league->members
            ->mapWithKeys(fn (LeagueMember $member) => [
                $member->id => Roster::slotLineup($template, $this->squad($picks->get($member->id) ?? collect())),
            ]);
    }

    /**
     * One team's picks, as the squad the roster facade slots.
     *
     * @param Collection<int, DraftPick> $picks
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function squad(Collection $picks): Collection
    {
        return $picks->map(fn (DraftPick $pick) => [
            'position' => $pick->player?->position_id,
            'player'   => [
                'player_id'   => $pick->player_id,
                'pick_id'     => $pick->id,
                'full_name'   => $pick->player?->full_name,
                'position_id' => $pick->player?->position_id,
                'team_id'     => $pick->player?->team_id,
                'amount'      => (int) $pick->amount,
                'round'       => $pick->round,
                'pick_number' => $pick->pick_number,
            ],
        ])->values();
    }
}
