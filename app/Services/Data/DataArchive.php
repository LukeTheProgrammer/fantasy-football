<?php

namespace App\Services\Data;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The one place that reads and writes pulled source data on disk.
 *
 * Every pull is filed under the date it was captured, so a dataset keeps its
 * own history and a later pull never overwrites an earlier one:
 *
 *     storage/data/{source}/{dataset}/{season}/week-{week}/{captured_on}/{name}.{extension}
 *
 * Reads default to the newest capture, which removes the need for the second
 * "latest" copy the old layout kept beside the dated directories.
 */
class DataArchive
{
    /**
     * Where a capture directory is named for the day it was pulled.
     */
    public const DATE_FORMAT = 'Y-m-d';

    public function __construct(
        private string $source,
        private string $dataset,
        private int $season,
        private int $week,
    ) {
        //
    }

    public static function for(string $source, string $dataset, int $season, int $week): self
    {
        return new self($source, $dataset, $season, $week);
    }

    /**
     * Write a capture and return the path it was written to.
     */
    public function put(string $name, string $contents, string $extension = 'json', ?string $capturedOn = null): string
    {
        $path = $this->path($name, $extension, $capturedOn ?? $this->today());

        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, $contents);

        return $path;
    }

    /**
     * Write a capture as pretty printed JSON.
     */
    public function putJson(string $name, array $data, ?string $capturedOn = null): string
    {
        return $this->put($name, json_encode($data, JSON_PRETTY_PRINT), 'json', $capturedOn);
    }

    /**
     * The newest capture of a file, or the capture from a given date.
     */
    public function get(string $name, string $extension = 'json', ?string $capturedOn = null): ?string
    {
        $path = $this->pathTo($name, $extension, $capturedOn);

        return $path ? file_get_contents($path) : null;
    }

    /**
     * The newest capture of a JSON file, decoded.
     */
    public function getJson(string $name, ?string $capturedOn = null): ?array
    {
        $contents = $this->get($name, 'json', $capturedOn);

        if (empty($contents)) {
            return null;
        }

        $data = json_decode($contents, true);

        return is_array($data) ? $data : null;
    }

    /**
     * Whether a capture of this file exists at all.
     */
    public function has(string $name, string $extension = 'json', ?string $capturedOn = null): bool
    {
        return $this->pathTo($name, $extension, $capturedOn) !== null;
    }

    /**
     * Whether this file was already captured today.
     */
    public function capturedToday(string $name, string $extension = 'json'): bool
    {
        return file_exists($this->path($name, $extension, $this->today()));
    }

    /**
     * The path a capture would be written to.
     */
    public function path(string $name, string $extension, string $capturedOn): string
    {
        return $this->directory($capturedOn) . '/' . $name . '.' . $extension;
    }

    /**
     * The path a capture can be read from, newest first, or null when the file
     * was never captured.
     *
     * Falls back to the flat file the pre archive layout wrote beside the dated
     * directories, so existing pulls stay readable.
     */
    public function pathTo(string $name, string $extension = 'json', ?string $capturedOn = null): ?string
    {
        if ($capturedOn) {
            $path = $this->path($name, $extension, $capturedOn);

            return file_exists($path) ? $path : null;
        }

        foreach ($this->captures() as $date) {
            $path = $this->path($name, $extension, $date);

            if (file_exists($path)) {
                return $path;
            }
        }

        $legacy = $this->weekDirectory() . '/' . $name . '.' . $extension;

        return file_exists($legacy) ? $legacy : null;
    }

    /**
     * Capture dates for this season and week, newest first.
     *
     * @return Collection<int, string>
     */
    public function captures(): Collection
    {
        $weekDirectory = $this->weekDirectory();

        if (!is_dir($weekDirectory)) {
            return collect();
        }

        return collect(scandir($weekDirectory))
            ->filter(fn ($entry) => is_dir($weekDirectory . '/' . $entry))
            ->filter(fn ($entry) => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $entry))
            ->sortDesc()
            ->values();
    }

    /**
     * The directory a capture from this date lives in.
     */
    public function directory(?string $capturedOn = null): string
    {
        return $this->weekDirectory() . '/' . ($capturedOn ?? $this->today());
    }

    private function weekDirectory(): string
    {
        return storage_path(implode('/', [
            'data',
            $this->source,
            $this->dataset,
            $this->season,
            'week-' . $this->week,
        ]));
    }

    private function today(): string
    {
        return Carbon::today()->format(self::DATE_FORMAT);
    }
}
