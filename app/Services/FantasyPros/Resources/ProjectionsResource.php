<?php

namespace App\Services\FantasyPros\Resources;

use App\Enums\FantasyProsSlate;
use App\Models\Season;
use App\Models\Week;
use App\Services\Data\DataArchive;
use App\Services\FantasyPros\Concerns\ParsesEcrData;
use App\Traits\HasDataFormats;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Pulls FantasyPros consensus pages and files them in the archive.
 *
 * This is the fetch stage: it gets bytes, keeps them, and hands back the player
 * rows embedded in the page. It does not touch models, and the only place it
 * writes is through the archive.
 */
class ProjectionsResource extends BaseResource
{
    use HasDataFormats;
    use ParsesEcrData;

    public const SOURCE = 'fantasy-pros';

    public const DATASET = 'projections';

    /**
     * Slate value to rankings url, kept for callers that still work in labels.
     *
     * @var array<string, string>
     */
    public array $sources = [];

    private ?int $currentSeason = null;

    private ?int $currentWeek = null;

    public function __construct()
    {
        foreach (FantasyProsSlate::cases() as $slate) {
            $this->sources[$slate->value] = $slate->rankingsUrl();
        }

        $this->currentSeason = Season::current()->first()?->id;
        $this->currentWeek = Week::current()->first()?->week;
    }

    /**
     * Every slate for a season and week, keyed by slate.
     *
     * @return array<string, array>
     */
    public function getProjections(?int $season = null, ?int $week = null): array
    {
        $projections = [];

        foreach (FantasyProsSlate::cases() as $slate) {
            $projections[$slate->value] = $this->getProjection($slate, $season, $week);
        }

        return $projections;
    }

    /**
     * One slate's player rows, pulled first if today's capture is missing.
     *
     * @return array|false
     */
    public function getProjection(string|FantasyProsSlate $slate, ?int $season = null, ?int $week = null)
    {
        $slate = $this->slate($slate);
        $season ??= $this->currentSeason;
        $week ??= $this->currentWeek;

        $archive = $this->archive($season, $week);

        if ($this->shouldPull($archive, $slate)) {
            $this->pull($slate, $season, $week);
        }

        $players = $archive->getJson($slate->value);

        return empty($players) ? false : $players;
    }

    /**
     * Pull a slate from FantasyPros and file both the page and the rows parsed
     * out of it under today's date.
     *
     * @return array|false
     */
    public function pull(FantasyProsSlate $slate, ?int $season = null, ?int $week = null)
    {
        $season ??= $this->currentSeason;
        $week ??= $this->currentWeek;

        $response = Http::get($slate->rankingsUrl());

        if (!$response->successful()) {
            Log::error('FantasyPros pull failed', [
                'slate'  => $slate->value,
                'url'    => $slate->rankingsUrl(),
                'status' => $response->status(),
            ]);

            return false;
        }

        $html = $response->body();
        $players = $this->parseEcrData($html);

        $archive = $this->archive($season, $week);

        $archive->put($slate->value, $html, 'html');
        $archive->putJson($slate->value, $players);

        return $players;
    }

    /**
     * Read a slate straight from the archive, without pulling.
     *
     * @return array|null
     */
    public function fromArchive(
        string|FantasyProsSlate $slate,
        ?int $season = null,
        ?int $week = null,
        ?string $capturedOn = null
    ): ?array {
        $slate = $this->slate($slate);

        return $this->archive($season ?? $this->currentSeason, $week ?? $this->currentWeek)
            ->getJson($slate->value, $capturedOn);
    }

    /**
     * A capture is taken once a day, so the rest of the day reads from disk.
     */
    private function shouldPull(DataArchive $archive, FantasyProsSlate $slate): bool
    {
        if ($this->forcePull) {
            return true;
        }

        return !$archive->capturedToday($slate->value);
    }

    private function archive(int $season, int $week): DataArchive
    {
        return DataArchive::for(self::SOURCE, self::DATASET, $season, $week);
    }

    private function slate(string|FantasyProsSlate $slate): FantasyProsSlate
    {
        if ($slate instanceof FantasyProsSlate) {
            return $slate;
        }

        return FantasyProsSlate::tryFrom($slate)
            ?? throw new InvalidArgumentException('Invalid slate: ' . $slate);
    }
}
