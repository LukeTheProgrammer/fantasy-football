<?php

namespace App\Services\Auction\Actions;

use App\Models\Draft;
use App\Models\LeagueMember;
use Illuminate\Support\Collection;

/**
 * What each team can still do: money left, spots left, and the most they could
 * bid on the player currently up.
 */
class SummariseTeamsAction
{
    /**
     * The minimum bid every remaining roster spot must be able to cover.
     */
    public const MINIMUM_BID = 1;

    public function run(Draft $draft): Collection
    {
        $budget = (int) ($draft->auction_budget ?? 0);
        $rosterSize = (int) ($draft->league->settings?->roster_size ?? 0);

        $picks = $draft->picks->groupBy('league_member_id');

        return $draft->league->members
            ->map(function (LeagueMember $member) use ($picks, $budget, $rosterSize) {
                $teamPicks = $picks->get($member->id) ?? collect();

                $spent = (int) $teamPicks->sum('amount');
                $filled = $teamPicks->count();
                $remaining = $budget - $spent;
                $openSpots = max(0, $rosterSize - $filled);

                return [
                    'id'         => $member->id,
                    'team_name'  => $member->team_name,
                    'owner_name' => $member->owner_name,
                    'spent'      => $spent,
                    'remaining'  => $remaining,
                    'filled'     => $filled,
                    'open_spots' => $openSpots,
                    // Every other open spot still needs a dollar, so the most
                    // this team can put on one player is what is left after
                    // covering them.
                    'max_bid' => $openSpots > 0
                        ? max(0, $remaining - (($openSpots - 1) * self::MINIMUM_BID))
                        : 0,
                ];
            })
            ->sortBy('team_name')
            ->values();
    }
}
