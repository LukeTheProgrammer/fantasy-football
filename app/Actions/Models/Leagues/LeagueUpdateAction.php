<?php

namespace App\Actions\Models\Leagues;

use App\Enums\FantasyPlatforms;
use App\Models\League;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LeagueUpdateAction
{
    /**
     * Update only the fields the caller actually sent.
     *
     * Reading a missing field back off the model would write whatever the
     * in-memory copy happens to hold, so an unhydrated or stale attribute
     * becomes fact. Absent keys are left untouched instead.
     *
     * The slug is deliberately not rewritten. It is an identifier, and an
     * imported league's slug carries its platform and season (espn-name-2026),
     * which a name based slug would strip -- colliding with the same league's
     * next season on a unique index.
     *
     * Credentials replace wholesale rather than merge: a rotated cookie must
     * not leave the previous one behind, and a platform switch must not leave
     * the old platform's keys in place.
     */
    public function run(League $league, array $data): League
    {
        $attributes = Arr::only($data, [
            'name',
            'description',
            'team_count',
            'is_public',
            'join_code',
            'is_active',
            'platform',
            'credentials',
        ]);

        // The API names platforms in lower case; the column stores the enum.
        if (isset($attributes['platform'])) {
            $attributes['platform'] = FantasyPlatforms::from(Str::upper($attributes['platform']))->value;
        }

        $league->update($attributes);

        return $league->refresh();
    }
}
