<?php

namespace App\Actions\Models\Leagues;

use App\Models\League;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LeagueCreateAction
{
    public function run(User $creator, array $data): League
    {
        $league = League::create([
            'created_by_user_id' => $creator->id,
            'name'               => Arr::get($data, 'name'),
            'slug'               => Str::slug(Arr::get($data, 'name')),
            'description'        => Arr::get($data, 'description'),
            'team_count'         => Arr::get($data, 'team_count'),
            'is_public'          => Arr::get($data, 'is_public'),
            'join_code'          => Str::upper(Str::random(8)),
            'draft_type'         => Arr::get($data, 'draft_type'),
            'draft_date'         => Arr::get($data, 'draft_date'),
            'is_active'          => true,
        ]);

        return $league;
    }
}
