<?php

namespace App\Services\Picks\Actions;

use App\Facades\Roster;
use App\Models\Draft;
use App\Models\DraftRanking;
use App\Models\LeagueMember;
use App\Models\LeagueMemberRoster;
use Illuminate\Support\Collection;

/**
 * Places each team's squad into the roster the league is configured for.
 *
 * Keepers and picks are one squad here: a keeper cost no pick, but he plays
 * exactly like one, so both are slotted together. Who starts is decided by the
 * draft rankings rather than by pick order, because a player kept in advance
 * has no pick number to be judged by.
 */
class SlotRostersAction
{
    public function run(Draft $draft, float $ppr, bool $superflex): Collection
    {
        $template = $draft->league->settings?->roster_positions ?? [];

        $entries = $this->entries($draft);

        $ranks = $this->ranks($draft, $entries->pluck('player_id')->filter()->unique(), $ppr, $superflex);

        // Best first, so the lineup is the best nine a team holds and the rest
        // fall to the bench. A player the rankings do not carry sorts last
        // rather than jumping the queue.
        $squads = $entries
            ->sortBy(fn (array $entry) => $ranks[$entry['player_id']] ?? PHP_INT_MAX)
            ->groupBy('league_member_id');

        $picks = $draft->picks->groupBy('league_member_id');

        return $draft->league->members->map(function (LeagueMember $member) use ($template, $squads, $picks) {
            return [
                'league_member_id' => $member->id,
                'external_id'      => $member->external_id,
                'team_name'        => $member->team_name,
                'owner_name'       => $member->owner_name,
                'slots'            => Roster::slotLineup($template, $this->squad($squads->get($member->id) ?? collect())),
                // The lineup is ordered by rank, so the picks are carried
                // separately for the board's own running order.
                'picks' => $picks->get($member->id, collect())
                    ->sortBy('overall_pick_number')
                    ->map(fn ($pick) => [
                        'pick_id'             => $pick->id,
                        'player_id'           => $pick->player_id,
                        'full_name'           => $pick->player?->full_name,
                        'position'            => $pick->player?->position_id,
                        'team'                => $pick->player?->team_id,
                        'round'               => $pick->round,
                        'overall_pick_number' => $pick->overall_pick_number,
                    ])
                    ->values(),
            ];
        })->values();
    }

    /**
     * One team's squad, as the roster facade slots it.
     *
     * @param Collection<int, array<string, mixed>> $entries
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function squad(Collection $entries): Collection
    {
        return $entries->map(fn (array $entry) => [
            'position' => $entry['position'],
            'player'   => $entry,
        ])->values();
    }

    /**
     * Every player a team holds, however he was come by.
     */
    private function entries(Draft $draft): Collection
    {
        $keepers = LeagueMemberRoster::whereIn('league_member_id', $draft->league->members->pluck('id'))
            ->where('season', $draft->league->season_id)
            ->where('week', 0)
            ->with(['player'])
            ->get()
            ->map(fn (LeagueMemberRoster $row) => [
                'league_member_id' => $row->league_member_id,
                'player_id'        => $row->player_id,
                'pick_id'          => null,
                'full_name'        => $row->player?->full_name,
                'position'         => $row->player?->position_id,
                'team'             => $row->player?->team_id,
                'source'           => 'Keeper',
            ]);

        $picks = $draft->picks->map(fn ($pick) => [
            'league_member_id' => $pick->league_member_id,
            'player_id'        => $pick->player_id,
            'pick_id'          => $pick->id,
            'full_name'        => $pick->player?->full_name,
            'position'         => $pick->player?->position_id,
            'team'             => $pick->player?->team_id,
            'source'           => 'R' . $pick->round . '.' . $pick->pick_number,
        ]);

        return $keepers->concat($picks);
    }

    /**
     * Rank per player id, in the league's own scoring format.
     *
     * @return array<int, int>
     */
    private function ranks(Draft $draft, Collection $playerIds, float $ppr, bool $superflex): array
    {
        if ($playerIds->isEmpty()) {
            return [];
        }

        return DraftRanking::query()
            ->latestRanking($draft->league->season_id, $ppr, $superflex)
            ->forFormat($ppr, $superflex)
            ->whereIn('player_id', $playerIds)
            ->where('rank', '>', 0)
            ->pluck('rank', 'player_id')
            ->all();
    }
}
