<?php

namespace App\Actions\Models\PlayerNotFound;

use App\Models\PlayerNotFound;

class PlayerNotFoundUpsertAction
{
    public function run(array $data = [], ?string $source = null): PlayerNotFound
    {
        return PlayerNotFound::create([
            'source_class' => $source,
            'source_data' => $data,
        ]);
    }
}
