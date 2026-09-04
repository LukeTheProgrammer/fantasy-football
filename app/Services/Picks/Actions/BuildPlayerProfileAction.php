<?php

namespace App\Services\Picks\Actions;

use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\DraftRanking;
use App\Models\LeagueMemberRoster;
use App\Models\Player;

/**
 * The little that is worth knowing about one player mid draft.
 *
 * Fetched on demand rather than shipped with the board, because the room only
 * ever looks at one player at a time and the board already costs it enough.
 */
class BuildPlayerProfileAction
{
    /**
     * @return array<string, mixed>
     */
    public function run(Draft $draft, Player $player, float $ppr, bool $superflex): array
    {
        return [
            'player_id' => $player->id,
            'full_name' => $player->full_name,
            'position'  => $player->position_id,
            'team'      => $player->team_id,
            'headshot'  => $player->headshot,
            'jersey'    => $player->jersey_number,
            'age'       => $player->birth_date?->age,
            'height'    => $player->height,
            'weight'    => $player->weight,
            'college'   => $player->college,
            'ranking'   => $this->ranking($draft, $player, $ppr, $superflex),
            'owner'     => $this->owner($draft, $player),
        ];
    }

    /**
     * Where the board has him, in the league's own scoring format.
     *
     * @return array<string, mixed>|null
     */
    private function ranking(Draft $draft, Player $player, float $ppr, bool $superflex): ?array
    {
        $ranking = DraftRanking::query()
            ->latestRanking($draft->league->season_id, $ppr, $superflex)
            ->forFormat($ppr, $superflex)
            ->where('player_id', $player->id)
            ->first();

        if (!$ranking instanceof DraftRanking) {
            return null;
        }

        return [
            'rank' => $ranking->rank,
            'tier' => $ranking->tier,
            'adp'  => $ranking->adp,
            'adv'  => $ranking->adv,
        ];
    }

    /**
     * Whose he is, and how they came by him.
     *
     * @return array<string, mixed>|null
     */
    private function owner(Draft $draft, Player $player): ?array
    {
        $pick = $draft->picks()->with('leagueMember')->where('player_id', $player->id)->first();

        if ($pick instanceof DraftPick) {
            return [
                'team_name' => $pick->leagueMember?->team_name,
                'source'    => 'R' . $pick->round . '.' . $pick->pick_number,
            ];
        }

        $keeper = LeagueMemberRoster::with('leagueMember')
            ->whereIn('league_member_id', $draft->league->members->pluck('id'))
            ->where('season', $draft->league->season_id)
            ->where('week', 0)
            ->where('player_id', $player->id)
            ->first();

        if ($keeper instanceof LeagueMemberRoster) {
            return [
                'team_name' => $keeper->leagueMember?->team_name,
                'source'    => 'Keeper',
            ];
        }

        return null;
    }
}
