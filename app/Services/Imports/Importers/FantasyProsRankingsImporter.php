<?php

namespace App\Services\Imports\Importers;

use App\Enums\Datum;
use App\Enums\FantasyProsDraftSlate;
use App\Facades\FantasyPros;
use App\Facades\Player as PlayerFacade;
use App\Models\DraftRanking;
use App\Models\Player;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Writes archived FantasyPros draft boards into draft rankings.
 *
 * Each capture is stored under the date it was taken rather than replacing the
 * last one, so a player's draft stock over a season is a query rather than a
 * lost history.
 */
class FantasyProsRankingsImporter
{
    public const SOURCE = Datum::SOURCE_FANTASY_PROS;

    /**
     * @var array<int, array>
     */
    private array $errors = [];

    private int $created = 0;

    private int $updated = 0;

    /**
     * Import every board captured for a season.
     *
     * @param int $season
     * @param array<int, FantasyProsDraftSlate>|null $slates
     * @param string|null $capturedOn Read a specific capture instead of the newest.
     *
     * @return array{created: int, updated: int, skipped: int, errors: array}
     */
    public function import(int $season, ?array $slates = null, ?string $capturedOn = null): array
    {
        $rankings = FantasyPros::rankings();

        $rankedAt = $capturedOn ?? $rankings->captures($season)->first();

        if (empty($rankedAt)) {
            $this->addError('No captures on disk', ['season' => $season]);

            return $this->result();
        }

        foreach ($slates ?? FantasyProsDraftSlate::cases() as $slate) {
            $players = $rankings->fromArchive($slate, $season, $rankedAt);

            if (empty($players)) {
                $this->addError('No capture on disk', ['slate' => $slate->value]);

                continue;
            }

            foreach ($players as $playerData) {
                $this->save($slate, $playerData, $season, $rankedAt);
            }
        }

        return $this->result();
    }

    /**
     * Store one row's rank for the format its board was scored under.
     */
    private function save(FantasyProsDraftSlate $slate, array $playerData, int $season, string $rankedAt): void
    {
        $player = PlayerFacade::find([
            'fp_id'       => Arr::get($playerData, 'player_id'),
            'full_name'   => Arr::get($playerData, 'player_name'),
            'position_id' => Arr::get($playerData, 'player_position_id'),
            'team_id'     => Arr::get($playerData, 'player_team_id'),
        ], ['source' => static::class]);

        if (!$player instanceof Player) {
            $this->addError('Player not found', $playerData);

            return;
        }

        $ranking = DraftRanking::updateOrCreate([
            'player_id' => $player->id,
            'season'    => $season,
            'ranked_at' => Carbon::parse($rankedAt)->toDateString(),
            'type'      => $slate->type(),
            'source'    => self::SOURCE->value,
            'ppr'       => $slate->ppr(),
            'superflex' => $slate->isSuperflex(),
        ], [
            'rank' => intval(Arr::get($playerData, 'rank_ecr')),
            'tier' => intval(Arr::get($playerData, 'tier')) ?: null,
        ]);

        $ranking->wasRecentlyCreated ? $this->created++ : $this->updated++;
    }

    private function addError(string $reason, array $data): void
    {
        $this->errors[] = ['reason' => $reason, 'data' => $data];
    }

    /**
     * @return array{created: int, updated: int, skipped: int, errors: array}
     */
    private function result(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => count($this->errors),
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
