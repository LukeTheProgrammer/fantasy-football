<?php

namespace App\Actions\Models\DraftPick;

use App\Models\DraftPick;
use Illuminate\Support\Arr;

class DraftPickUpsertAction
{
    /**
     * A pick is one player in one draft, which is what the table's unique key
     * says, so that pair is what a pick is matched on.
     *
     * A slot the team gave up has no player to be matched on, so it is keyed on
     * where it sits in the order instead — otherwise every pass in a draft
     * reads as the same row.
     */
    public function run(array $data = []): DraftPick
    {
        $playerId = Arr::get($data, 'player_id');

        $find = $playerId === null
            ? [
                'draft_id'            => Arr::get($data, 'draft_id'),
                'overall_pick_number' => Arr::get($data, 'overall_pick_number'),
            ]
            : [
                'draft_id'  => Arr::get($data, 'draft_id'),
                'player_id' => $playerId,
            ];

        $values = [
            'league_member_id'    => Arr::get($data, 'league_member_id'),
            'round'               => Arr::get($data, 'round'),
            'pick_number'         => Arr::get($data, 'pick_number'),
            'overall_pick_number' => Arr::get($data, 'overall_pick_number'),
            'amount'              => Arr::get($data, 'amount'),
            'is_keeper'           => Arr::get($data, 'is_keeper', false),
        ];

        $values = array_filter($values, fn ($value) => $value !== null);

        // A pass is written as a slot with nobody in it, which array_filter
        // would otherwise drop straight back out of the row.
        if ($playerId === null) {
            $values['player_id'] = null;
        }

        return DraftPick::updateOrCreate($find, $values);
    }
}
