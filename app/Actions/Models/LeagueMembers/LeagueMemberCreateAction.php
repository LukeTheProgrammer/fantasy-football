<?php

namespace App\Actions\Models\LeagueMembers;

use App\Models\League;
use App\Models\LeagueMember;
use App\Models\User;
use Illuminate\Support\Arr;

class LeagueMemberCreateAction
{
    public function run(League $league, ?User $user = null, array $data = []): LeagueMember
    {
        $member = LeagueMember::create([
            'league_id' => $league->id,
            'user_id'   => ($user) ? $user->id : null,
            // The platform's own id for the team. A sale off the draft socket
            // names a team by this and nothing else.
            'external_id' => Arr::get($data, 'external_id'),
            'team_name'   => Arr::get($data, 'team_name', 'New Team'),
            'owner_name'  => Arr::get($data, 'owner_name', 'New Owner'),
            'team_logo'   => Arr::get($data, 'team_logo', null),
            'is_admin'    => Arr::get($data, 'is_admin', false),
            'is_active'   => Arr::get($data, 'is_active', true),
        ]);

        return $member;
    }
}
