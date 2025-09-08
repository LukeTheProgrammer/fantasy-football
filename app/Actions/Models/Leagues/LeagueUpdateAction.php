<?php

namespace App\Actions\Models\Leagues;

use App\Models\League;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LeagueUpdateAction
{
    public function run(League $league, array $data): League
    {
        $league->update([
            'name'        => Arr::get($data, 'name', $league->name),
            'slug'        => Str::slug(Arr::get($data, 'name', $league->name)),
            'description' => Arr::get($data, 'description', $league->description),
            'team_count'  => Arr::get($data, 'team_count', $league->team_count),
            'is_public'   => Arr::get($data, 'is_public', $league->is_public),
            'join_code'   => Arr::get($data, 'join_code', $league->join_code),
            'draft_type'  => Arr::get($data, 'draft_type', $league->draft_type),
            'draft_date'  => Arr::get($data, 'draft_date', $league->draft_date),
            'is_active'   => Arr::get($data, 'is_active', $league->is_active),
        ]);

        return $league->refresh();
    }
}
