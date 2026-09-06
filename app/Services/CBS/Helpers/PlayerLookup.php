<?php

namespace App\Services\CBS\Helpers;

use App\Enums\NFLPositions;
use App\Enums\NFLTeams;
use App\Models\Team;
use Illuminate\Support\Arr;

/**
 * What to look a CBS player up by.
 *
 * CBS carries no id this app stores, so a player is resolved on his name — and
 * for a team defense CBS publishes only the nickname, "Rams", where every other
 * source and this app's own row say "Los Angeles Rams". A defense is therefore
 * identified by the team it belongs to rather than by what CBS calls it.
 */
class PlayerLookup
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public static function of(array $data): array
    {
        if (Arr::get($data, 'position_id') !== NFLPositions::DST->value) {
            return $data;
        }

        $team = Team::find(NFLTeams::fromAbbreviation(Arr::get($data, 'team_id'))?->value);

        if (!$team instanceof Team) {
            return $data;
        }

        $data['full_name'] = trim($team->location . ' ' . $team->name);

        return $data;
    }
}
