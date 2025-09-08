<?php

namespace App\Actions\Models\LeagueMembers;

use App\Models\LeagueMember;
use Illuminate\Support\Arr;

class LeagueMemberUpdateAction
{
    public function run(LeagueMember $member, array $data = []): LeagueMember
    {
        $member->update([
            'user_id'   => Arr::get($data, 'user_id', $member->user_id),
            'team_name' => Arr::get($data, 'team_name', $member->team_name),
            'is_admin'  => Arr::get($data, 'is_admin', $member->is_admin),
            'is_active' => Arr::get($data, 'is_active', $member->is_active),
        ]);

        return $member->refresh();
    }
}
