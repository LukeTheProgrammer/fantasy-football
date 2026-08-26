<?php

namespace App\Services\Nflverse\Resources;

use App\Models\Season;
use App\Services\Data\DataArchive;
use App\Services\Nflverse\Enums\NflverseDataset;
use App\Services\Nflverse\Helpers\Csv;
use App\Services\Nflverse\Helpers\NflverseRelease;
use App\Traits\HasDataFormats;
use Generator;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Downloads one nflverse file and reads it back.
 *
 * A finished season's numbers never change, so once a season has been captured
 * it is read from disk forever. Only the season in progress is pulled again,
 * and then once a day, which is as often as nflverse rebuilds it.
 */
abstract class BaseResource
{
    use HasDataFormats;

    public const SOURCE = 'nflverse';

    /**
     * These files are seasonal rather than weekly, so every capture is filed
     * under week zero.
     */
    public const WEEK = 0;

    abstract public function dataset(): NflverseDataset;

    /**
     * The rows of a file, downloading it first if the archive has no usable
     * capture.
     *
     * @return Generator<int, array<string, string>>
     */
    protected function read(?int $season = null, string $window = 'week'): Generator
    {
        $archive = $this->archive($season);
        $name = $this->name($season, $window);

        if ($this->shouldPull($archive, $name, $season)) {
            $this->pull($archive, $name, $season, $window);
        }

        $path = $archive->pathTo($name, 'csv');

        if ($path === null) {
            throw new RuntimeException('No capture of ' . $name . ' could be read.');
        }

        return (new Csv)->rows($path);
    }

    /**
     * Download the file and file it under today's date.
     */
    protected function pull(DataArchive $archive, string $name, ?int $season, string $window): void
    {
        $url = (new NflverseRelease)->url($this->dataset(), $season, $window);

        $response = Http::timeout(120)->get($url);

        if (!$response->successful()) {
            throw new RuntimeException(
                'nflverse returned ' . $response->status() . ' for ' . $url
            );
        }

        $archive->put($name, $response->body(), 'csv');
    }

    /**
     * A season that has finished is captured once; the one in progress is
     * captured daily.
     */
    protected function shouldPull(DataArchive $archive, string $name, ?int $season): bool
    {
        if ($this->forcePull) {
            return true;
        }

        if (!$archive->has($name, 'csv')) {
            return true;
        }

        return !$this->isComplete($season) && !$archive->capturedToday($name, 'csv');
    }

    /**
     * Whether a season is over, and so will never be republished.
     */
    protected function isComplete(?int $season): bool
    {
        if ($season === null) {
            return false;
        }

        $current = Season::current()->first()?->id;

        return $current !== null && $season < (int) $current;
    }

    protected function archive(?int $season): DataArchive
    {
        return DataArchive::for(
            self::SOURCE,
            $this->dataset()->value,
            $season ?? 0,
            self::WEEK
        );
    }

    /**
     * The archived file's name, which is the release asset's name without its
     * extension.
     */
    protected function name(?int $season, string $window): string
    {
        return pathinfo($this->dataset()->file($season, $window), PATHINFO_FILENAME);
    }
}
