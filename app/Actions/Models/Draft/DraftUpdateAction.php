<?php

namespace App\Actions\Models\Draft;

use App\Models\Draft;
use Illuminate\Support\Arr;

class DraftUpdateAction
{
    /**
     * Update only the fields the caller actually sent.
     *
     * Reading a missing field back off the model would write whatever the
     * in-memory copy happens to hold, so an absent key is left alone rather
     * than rewritten from a stale attribute.
     */
    public function run(Draft $draft, array $data): Draft
    {
        $draft->update(Arr::only($data, [
            'draft_date',
            'draft_type',
            'is_completed',
            'auction_budget',
            'current_pick',
            'current_round',
            'time_per_pick',
            'is_active',
        ]));

        return $draft->refresh();
    }
}
