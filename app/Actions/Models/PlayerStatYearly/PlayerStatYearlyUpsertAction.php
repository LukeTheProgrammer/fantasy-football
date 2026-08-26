<?php

namespace App\Actions\Models\PlayerStatYearly;

use App\Models\PlayerStatYearly;

class PlayerStatYearlyUpsertAction
{
    /**
     * The columns that identify a season line rather than describe one.
     *
     * The source is part of the identity so a total taken from a source can sit
     * beside one summed from weekly rows, which is how the two get compared.
     *
     * @var array<int, string>
     */
    public const UNIQUE_BY = ['player_id', 'season', 'season_type', 'source'];

    /**
     * Write one season line, or a list of them in a single statement.
     *
     * @param array<string, mixed>|array<int, array<string, mixed>> $data
     */
    public function run(array $data): PlayerStatYearly|int
    {
        if (array_is_list($data)) {
            return $this->many($data);
        }

        return PlayerStatYearly::updateOrCreate(
            array_intersect_key($data, array_flip(self::UNIQUE_BY)),
            $data
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function many(array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $now = now();

        $rows = array_map(
            fn (array $row) => $row + ['created_at' => $now, 'updated_at' => $now],
            $rows
        );

        // Every row carries the same shape, so the columns to overwrite are
        // whatever is not part of the identity.
        $update = array_values(array_diff(array_keys($rows[0]), self::UNIQUE_BY, ['created_at']));

        PlayerStatYearly::upsert($rows, self::UNIQUE_BY, $update);

        return count($rows);
    }
}
