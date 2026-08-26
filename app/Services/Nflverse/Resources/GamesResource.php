<?php

namespace App\Services\Nflverse\Resources;

use App\Enums\Datum;
use App\Services\Nflverse\Enums\NflverseDataset;
use App\Services\Nflverse\Formatters\GameFormatter;
use Generator;

/**
 * Every NFL game since 1999, with the ids ESPN and Pro Football Reference use
 * for the same game.
 *
 * One file covers every season, so it is filed under season zero and pulled
 * fresh each day rather than once per season.
 */
class GamesResource extends BaseResource
{
    public function dataset(): NflverseDataset
    {
        return NflverseDataset::SCHEDULES;
    }

    /**
     * @return Generator<int, array<string, mixed>>
     */
    public function getGames(?int $season = null): Generator
    {
        $formatter = new GameFormatter;

        foreach ($this->read() as $row) {
            if ($season !== null && (int) $row['season'] !== $season) {
                continue;
            }

            if ($this->dataFormat === Datum::FORMAT_RAW->value) {
                yield $row;

                continue;
            }

            yield $formatter->format($row);
        }
    }
}
