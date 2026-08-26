<?php

namespace App\Actions\Models\Draft;

use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\League;
use Illuminate\Support\Arr;

class DraftCreateAction
{
    public function run(League $league, array $data = []): Draft
    {
        $draft = Draft::create([
            'league_id'      => $league->id,
            'draft_date'     => Arr::get($data, 'draft_date'),
            'draft_type'     => Arr::get($data, 'draft_type', 'snake'),
            'is_completed'   => Arr::get($data, 'is_completed', false),
            'auction_budget' => Arr::get($data, 'auction_budget', null),
            'current_pick'   => Arr::get($data, 'current_pick', null),
            'current_round'  => Arr::get($data, 'current_round', null),
            'time_per_pick'  => Arr::get($data, 'time_per_pick', 90),
            'is_active'      => Arr::get($data, 'is_active', false),
        ]);

        $this->createPicks($league, $draft);

        return $draft;
    }

    private function createPicks(League $league, Draft $draft): void
    {
        $members = $league->members->toArray();

        for ($round = 0; $round < $league->settings->roster_size; $round++) {
            foreach ($members as $pick => $member) {
                DraftPick::create([
                    'draft_id'         => $draft->id,
                    'league_member_id' => Arr::get($member, 'id'),
                    'round'            => $round + 1,
                    'pick_number'      => $pick + 1,
                ]);
            }
        }
    }
}
