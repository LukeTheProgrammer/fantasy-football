<?php

namespace App\Services\Nflverse\Resources;

use App\Enums\Datum;
use App\Services\Nflverse\Enums\NflverseDataset;
use App\Services\Nflverse\Formatters\PlayerFormatter;
use Generator;

/**
 * Every player nflverse knows about, with the ids each other source uses.
 *
 * This file is the crosswalk that lets one player be recognised whether he
 * arrived from ESPN, FantasyPros or a stat line.
 */
class PlayersResource extends BaseResource
{
    public function dataset(): NflverseDataset
    {
        return NflverseDataset::PLAYERS;
    }

    /**
     * @return Generator<int, array<string, mixed>>
     */
    public function getPlayers(): Generator
    {
        $formatter = new PlayerFormatter;

        foreach ($this->read() as $row) {
            if ($this->dataFormat === Datum::FORMAT_RAW->value) {
                yield $row;

                continue;
            }

            yield $formatter->format($row);
        }
    }
}
