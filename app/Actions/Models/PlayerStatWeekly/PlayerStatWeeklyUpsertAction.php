<?php

namespace App\Actions\Models\PlayerStatWeekly;

use App\Models\PlayerStatWeekly;

class PlayerStatWeeklyUpsertAction
{
    /**
     * The columns that identify a stat line rather than describe one.
     *
     * The game is deliberately not among them: a player has one line per week
     * whether or not the game row has been matched yet, so a later match
     * updates the row instead of adding a second one.
     *
     * @var array<int, string>
     */
    public const UNIQUE_BY = ['player_id', 'season', 'week', 'season_type', 'source'];

    /**
     * Write one stat line, or a list of them in a single statement.
     *
     * A season's import arrives a game or a week at a time, which is thousands
     * of rows; one query per row is the difference between seconds and minutes.
     *
     * @param array<string, mixed>|array<int, array<string, mixed>> $data
     */
    public function run(array $data): PlayerStatWeekly|int
    {
        if (array_is_list($data)) {
            return $this->many($data);
        }

        return PlayerStatWeekly::updateOrCreate(
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

        PlayerStatWeekly::upsert($rows, self::UNIQUE_BY, $update);

        return count($rows);
    }
}
