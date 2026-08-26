<?php

namespace App\Services\Imports\Importers;

use App\Enums\Datum;
use App\Facades\Player as PlayerFacade;
use App\Models\DraftRanking;
use App\Models\Player;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Writes ESPN's player pool into draft rankings as a board of its own.
 *
 * ESPN publishes an average auction value and an average draft position drawn
 * from its own leagues, which is a draft board like any other source's: the
 * order is what its drafters actually do rather than what an expert says they
 * should. It is stored beside the FantasyPros boards under its own source, so
 * neither can overwrite the other.
 *
 * Values are stored exactly as published. ESPN aggregates single quarterback
 * leagues, so in a superflex league its quarterback prices read low — that is
 * a fact about the market, not an error to correct on the way in.
 */
class EspnRankingsImporter
{
    public const SOURCE = Datum::SOURCE_ESPN;

    /**
     * The format ESPN's aggregate describes: its own default scoring, and not
     * superflex.
     */
    public const PPR = 0.0;

    public const SUPERFLEX = false;

    public const TYPE = 'redraft';

    private int $created = 0;

    private int $updated = 0;

    private int $skipped = 0;

    /**
     * @var array<int, array{reason: string, data: array}>
     */
    private array $errors = [];

    /**
     * Store one capture of the pool.
     *
     * @param Collection<int, array<string, mixed>>|array<int, array<string, mixed>> $players
     *
     * @return array{created: int, updated: int, skipped: int, errors: array}
     */
    public function import(int $season, Collection|array $players, ?string $rankedAt = null): array
    {
        $rankedAt = $rankedAt ?? now()->toDateString();

        // ESPN gives a draft position rather than a rank, so the board's order
        // is read off it: the most fancied player is the one drafted earliest.
        $ordered = collect($players)
            ->filter(fn (array $player) => (float) Arr::get($player, 'adp', 0) > 0)
            ->sortBy(fn (array $player) => (float) Arr::get($player, 'adp'))
            ->values();

        foreach ($ordered as $index => $player) {
            $this->save($player, $season, $rankedAt, $index + 1);
        }

        return $this->result();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function save(array $data, int $season, string $rankedAt, int $rank): void
    {
        $player = PlayerFacade::find(
            Arr::only($data, ['espn_id', 'full_name', 'position_id']),
            ['source' => static::class]
        );

        if (!$player instanceof Player) {
            $this->skipped++;
            $this->errors[] = [
                'reason' => 'Player not found',
                'data'   => Arr::only($data, ['espn_id', 'full_name', 'position_id']),
            ];

            return;
        }

        $keys = [
            'player_id' => $player->id,
            'season'    => $season,
            'type'      => self::TYPE,
            'source'    => self::SOURCE->value,
            'ppr'       => self::PPR,
            'superflex' => self::SUPERFLEX,
        ];

        // Matched with whereDate rather than updateOrCreate: ranked_at is cast
        // to a date on the way in but compared as a raw string on the way out,
        // so a plain match misses the row it just wrote and the day's second
        // run collides with its own unique key.
        $ranking = DraftRanking::query()
            ->where($keys)
            ->whereDate('ranked_at', $rankedAt)
            ->first();

        if ($ranking instanceof DraftRanking) {
            $this->updated++;
        } else {
            $ranking = new DraftRanking($keys + ['ranked_at' => $rankedAt]);
            $this->created++;
        }

        $ranking->fill([
            'rank' => $rank,
            'adp'  => Arr::get($data, 'adp'),
            'adv'  => Arr::get($data, 'adv'),
        ])->save();
    }

    /**
     * Whether a capture for this day is already stored.
     */
    public function capturedOn(int $season, string $date): bool
    {
        return DraftRanking::query()
            ->where('season', $season)
            ->where('source', self::SOURCE->value)
            ->whereDate('ranked_at', $date)
            ->exists();
    }

    /**
     * @return array{created: int, updated: int, skipped: int, errors: array}
     */
    private function result(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors'  => $this->errors,
        ];
    }

    /**
     * Unresolved rows grouped by reason, for a command to display.
     */
    public function errorSummary(): Collection
    {
        return collect($this->errors)->groupBy('reason');
    }
}
