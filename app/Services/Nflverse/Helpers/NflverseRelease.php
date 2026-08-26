<?php

namespace App\Services\Nflverse\Helpers;

use App\Services\Nflverse\Enums\NflverseDataset;

/**
 * Where a dataset's file lives.
 *
 * Release assets are served straight from GitHub with no key, no quota and no
 * rate limit worth pacing for, which is the whole reason this source replaced
 * scraping.
 */
class NflverseRelease
{
    public const BASE_URL = 'https://github.com/nflverse/nflverse-data/releases/download';

    public function url(NflverseDataset $dataset, ?int $season = null, string $window = 'week'): string
    {
        return implode('/', [
            rtrim(config('services.nflverse.base_url', self::BASE_URL), '/'),
            $dataset->value,
            $dataset->file($season, $window),
        ]);
    }
}
