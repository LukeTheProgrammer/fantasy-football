<?php

namespace App\Services\Picks\Actions;

use App\Models\Draft;
use App\Models\LeagueMember;
use App\Models\LeagueMemberRoster;
use Illuminate\Support\Collection;

/**
 * What each team holds: the keepers it came in with and the picks it has made.
 */
class SlotRostersAction
{
    public function run(Draft $draft): Collection
    {
        $keepers = LeagueMemberRoster::whereIn('league_member_id', $draft->league->members->pluck('id'))
            ->where('season', $draft->league->season_id)
            ->where('week', 0)
            ->with(['player.position', 'player.team'])
            ->get()
            ->groupBy('league_member_id');

        $picks = $draft->picks->groupBy('league_member_id');

        return $draft->league->members->map(function (LeagueMember $member) use ($keepers, $picks) {
            return [
                'league_member_id' => $member->id,
                'external_id'      => $member->external_id,
                'team_name'        => $member->team_name,
                'owner_name'       => $member->owner_name,
                'keepers'          => $keepers->get($member->id, collect())
                    ->map(fn (LeagueMemberRoster $row) => $this->player($row->player))
                    ->values(),
                'picks' => $picks->get($member->id, collect())
                    ->sortBy('overall_pick_number')
                    ->map(fn ($pick) => [
                        ...$this->player($pick->player),
                        'pick_id'             => $pick->id,
                        'round'               => $pick->round,
                        'overall_pick_number' => $pick->overall_pick_number,
                    ])
                    ->values(),
            ];
        })->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function player(?object $player): array
    {
        return [
            'player_id' => $player?->id,
            'full_name' => $player?->full_name,
            'position'  => $player?->position_id,
            'team'      => $player?->team_id,
        ];
    }
}
