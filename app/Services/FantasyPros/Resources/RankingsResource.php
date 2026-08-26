<?php

namespace App\Services\FantasyPros\Resources;

use App\Enums\FantasyProsDraftSlate;
use App\Models\Season;
use App\Services\Data\DataArchive;
use App\Services\FantasyPros\Concerns\ParsesEcrData;
use App\Traits\HasDataFormats;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Pulls FantasyPros overall draft boards and files them in the archive.
 *
 * Draft boards are not weekly, so every capture is filed under week zero and
 * separated by the date it was taken. A season of daily captures is what makes
 * a player's draft value trackable over time.
 */
class RankingsResource extends BaseResource
{
    use HasDataFormats;
    use ParsesEcrData;

    public const SOURCE = 'fantasy-pros';

    public const DATASET = 'rankings';

    /**
     * Draft boards are seasonal rather than weekly.
     */
    public const WEEK = 0;

    private ?int $currentSeason = null;

    public function __construct()
    {
        $this->currentSeason = Season::current()->first()?->id;
    }

    /**
     * Every board for a season, keyed by slate.
     *
     * @return array<string, array|false>
     */
    public function getRankings(?int $season = null): array
    {
        $rankings = [];

        foreach (FantasyProsDraftSlate::cases() as $slate) {
            $rankings[$slate->value] = $this->getRanking($slate, $season);
        }

        return $rankings;
    }

    /**
     * One board's player rows, pulled first if today's capture is missing.
     *
     * @return array|false
     */
    public function getRanking(string|FantasyProsDraftSlate $slate, ?int $season = null)
    {
        $slate = $this->slate($slate);
        $season ??= $this->currentSeason;

        $archive = $this->archive($season);

        if ($this->shouldPull($archive, $slate)) {
            $this->pull($slate, $season);
        }

        $players = $archive->getJson($slate->value);

        return empty($players) ? false : $players;
    }

    /**
     * Read a board straight from the archive, without pulling.
     */
    public function fromArchive(
        string|FantasyProsDraftSlate $slate,
        ?int $season = null,
        ?string $capturedOn = null
    ): ?array {
        return $this->archive($season ?? $this->currentSeason)
            ->getJson($this->slate($slate)->value, $capturedOn);
    }

    /**
     * Pull a board and file both the page and the rows parsed out of it under
     * today's date.
     *
     * @return array|false
     */
    public function pull(FantasyProsDraftSlate $slate, ?int $season = null)
    {
        $season ??= $this->currentSeason;

        $response = Http::get($slate->url());

        if (!$response->successful()) {
            Log::error('FantasyPros rankings pull failed', [
                'slate'  => $slate->value,
                'url'    => $slate->url(),
                'status' => $response->status(),
            ]);

            return false;
        }

        $html = $response->body();
        $players = $this->parseEcrData($html);

        $archive = $this->archive($season);

        $archive->put($slate->value, $html, 'html');
        $archive->putJson($slate->value, $players);

        return $players;
    }

    /**
     * The capture dates held for a season, newest first.
     */
    public function captures(?int $season = null)
    {
        return $this->archive($season ?? $this->currentSeason)->captures();
    }

    /**
     * A capture is taken once a day, so the rest of the day reads from disk.
     */
    private function shouldPull(DataArchive $archive, FantasyProsDraftSlate $slate): bool
    {
        if ($this->forcePull) {
            return true;
        }

        return !$archive->capturedToday($slate->value);
    }

    private function archive(int $season): DataArchive
    {
        return DataArchive::for(self::SOURCE, self::DATASET, $season, self::WEEK);
    }

    private function slate(string|FantasyProsDraftSlate $slate): FantasyProsDraftSlate
    {
        if ($slate instanceof FantasyProsDraftSlate) {
            return $slate;
        }

        return FantasyProsDraftSlate::tryFrom($slate)
            ?? throw new InvalidArgumentException('Invalid draft slate: ' . $slate);
    }
}
