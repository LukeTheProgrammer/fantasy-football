<?php

namespace App\Services\Nflverse\Resources;

use App\Enums\Datum;
use App\Services\Nflverse\Enums\NflverseDataset;
use App\Services\Nflverse\Extractors\PlayerStatsExtractor;
use App\Services\Nflverse\Formatters\PlayerStatsFormatter;
use Generator;

/**
 * Player stat lines for a season, weekly or totalled.
 */
class PlayerStatsResource extends BaseResource
{
    /**
     * The season windows nflverse publishes totals for.
     */
    public const WINDOW_WEEK = 'week';

    public const WINDOW_REGULAR = 'reg';

    public const WINDOW_POST = 'post';

    public function dataset(): NflverseDataset
    {
        return NflverseDataset::PLAYER_STATS;
    }

    /**
     * One season's rows in the current data format.
     *
     * @return Generator<int, array<string, mixed>>
     */
    public function getStats(int $season, string $window = self::WINDOW_WEEK): Generator
    {
        $extractor = new PlayerStatsExtractor;
        $formatter = new PlayerStatsFormatter;
        $weekly = $window === self::WINDOW_WEEK;

        foreach ($this->read($season, $window) as $row) {
            if ($this->dataFormat === Datum::FORMAT_RAW->value) {
                yield $row;

                continue;
            }

            $extracted = $extractor->extract($row, $season, $weekly);

            if ($this->dataFormat === Datum::FORMAT_EXTRACTED->value) {
                yield $extracted;

                continue;
            }

            yield $formatter->format($extracted, $weekly);
        }
    }
}
