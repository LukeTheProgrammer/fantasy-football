<?php

namespace App\Actions\Models\DraftPick;

use App\Models\DraftPick;
use Illuminate\Support\Arr;

class DraftPickUpsertAction
{
    /**
     * A pick is one player in one draft, which is what the table's unique key
     * says, so that pair is what a pick is matched on.
     */
    public function run(array $data = []): DraftPick
    {
        $find = [
            'draft_id'  => Arr::get($data, 'draft_id'),
            'player_id' => Arr::get($data, 'player_id'),
        ];

        $values = [
            'league_member_id'    => Arr::get($data, 'league_member_id'),
            'round'               => Arr::get($data, 'round'),
            'pick_number'         => Arr::get($data, 'pick_number'),
            'overall_pick_number' => Arr::get($data, 'overall_pick_number'),
            'amount'              => Arr::get($data, 'amount'),
            'is_keeper'           => Arr::get($data, 'is_keeper', false),
        ];

        return DraftPick::updateOrCreate($find, array_filter(
            $values,
            fn ($value) => $value !== null
        ));
    }
}
