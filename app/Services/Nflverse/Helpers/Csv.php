<?php

namespace App\Services\Nflverse\Helpers;

use Generator;

/**
 * Reads a CSV a row at a time.
 *
 * A season of weekly stats is twenty thousand rows of a hundred and fifty
 * columns; holding the parsed file in memory alongside the rows being built
 * from it is avoidable, so nothing here returns an array.
 */
class Csv
{
    /**
     * Each row keyed by column name.
     *
     * @return Generator<int, array<string, string>>
     */
    public function rows(string $path): Generator
    {
        if (!is_readable($path)) {
            return;
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            return;
        }

        try {
            $headers = fgetcsv($handle, 0, ',', '"', '');

            if ($headers === false) {
                return;
            }

            // nflverse writes a UTF-8 BOM on some files, which would otherwise
            // become part of the first column's name.
            $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);

            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                // A short row is a truncated download, not a row with missing
                // values, so it is not worth guessing at.
                if (count($row) !== count($headers)) {
                    continue;
                }

                yield array_combine($headers, $row);
            }
        } finally {
            fclose($handle);
        }
    }
}
