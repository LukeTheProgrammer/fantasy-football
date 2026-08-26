<?php

namespace App\Actions\Models\PlayerMissing;

use App\Models\PlayerMissing;
use Illuminate\Support\Arr;

class PlayerMissingUpsertAction
{
    /**
     * @param array $data The raw source payload, stored verbatim.
     * @param string|null $source The driver that could not resolve the player.
     * @param array $attributes The summary columns the missing-players UI reads:
     *                          unique_id_key, unique_id_value, name, position_id, team_id.
     */
    public function run(array $data = [], ?string $source = null, array $attributes = []): PlayerMissing
    {
        $values = [
            'source_class'    => $source,
            'source_data'     => $data,
            'unique_id_key'   => Arr::get($attributes, 'unique_id_key'),
            'unique_id_value' => Arr::get($attributes, 'unique_id_value'),
            'name'            => Arr::get($attributes, 'name'),
            'position_id'     => Arr::get($attributes, 'position_id'),
            'team_id'         => Arr::get($attributes, 'team_id'),
        ];

        $key = $values['unique_id_key'];
        $value = $values['unique_id_value'];

        // Without a stable id there is nothing to match on, so the row is
        // appended rather than merged.
        if (empty($key) || empty($value)) {
            return PlayerMissing::create($values);
        }

        return PlayerMissing::updateOrCreate(
            [
                'source_class'    => $source,
                'unique_id_key'   => $key,
                'unique_id_value' => $value,
            ],
            $values
        );
    }
}
