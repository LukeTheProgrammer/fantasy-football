<?php

namespace App\Actions\Models\PlayerMissing;

use App\Models\PlayerMissing;

class PlayerMissingUpsertAction
{
    public function run(array $data = [], ?string $source = null): PlayerMissing
    {
        return PlayerMissing::create([
            'source_class' => $source,
            'source_data' => $data,
        ]);
    }
}
