<?php

namespace App\Services\Imports\Importers;

use App\Enums\Datum;
use App\Enums\FantasyProsSlate;
use App\Facades\FantasyPros;
use App\Facades\Player as PlayerFacade;
use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerProjection;
use App\Models\Team;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Writes archived FantasyPros slates into player projections.
 *
 * The fetch stage has already put the rows on disk, so this reads what is
 * there, resolves each row to a player, and stores one projection per scoring
 * format. Nothing here reaches out to FantasyPros.
 */
class FantasyProsProjectionsImporter
{
    public const SOURCE = Datum::SOURCE_FANTASY_PROS;

    /**
     * Rows that could not be stored, keyed by reason.
     *
     * @var array<int, array>
     */
    private array $errors = [];

    private int $created = 0;

    private int $updated = 0;

    /**
     * Import every slate captured for a season and week.
     *
     * @param int $season
     * @param int $week
     * @param array<int, FantasyProsSlate>|null $slates
     * @param string|null $capturedOn Read a specific capture instead of the newest.
     *
     * @return array{created: int, updated: int, skipped: int, errors: array}
     */
    public function import(int $season, int $week, ?array $slates = null, ?string $capturedOn = null): array
    {
        $projections = FantasyPros::projections();

        foreach ($slates ?? FantasyProsSlate::cases() as $slate) {
            $players = $projections->fromArchive($slate, $season, $week, $capturedOn);

            if (empty($players)) {
                $this->addError('No capture on disk', ['slate' => $slate->value]);

                continue;
            }

            foreach ($players as $playerData) {
                $this->save($slate, $playerData, $season, $week);
            }
        }

        return $this->result();
    }

    /**
     * Store one row's projection for the format its slate was scored under.
     */
    private function save(FantasyProsSlate $slate, array $playerData, int $season, int $week): void
    {
        if (Arr::get($playerData, 'player_team_id') === 'FA') {
            return;
        }

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

        $projection = PlayerProjection::updateOrCreate([
            'player_id' => $player->id,
            'season'    => $season,
            'week'      => $week,
            'source'    => self::SOURCE->value,
            'ppr'       => $slate->ppr(),
            'superflex' => $slate->isSuperflex(),
        ], [
            'nfl_game_id'      => $this->findNflGame($player, $season, $week)?->id,
            'projected_points' => floatval(Arr::get($playerData, 'r2p_pts')),
            'pos_rank'         => intval(Arr::get($playerData, 'rank_ecr')),
            'pos_rank_min'     => intval(Arr::get($playerData, 'rank_min')),
            'pos_rank_max'     => intval(Arr::get($playerData, 'rank_max')),
            'pos_rank_avg'     => floatval(Arr::get($playerData, 'rank_ave')),
            'pos_rank_std'     => floatval(Arr::get($playerData, 'rank_std')),
        ]);

        $projection->wasRecentlyCreated ? $this->created++ : $this->updated++;
    }

    /**
     * The game this player's projection belongs to, when the schedule knows it.
     *
     * Preseason and rest of season projections have no game, so a miss is not
     * an error.
     */
    private function findNflGame(Player $player, int $season, int $week): ?NflGame
    {
        if (!$player->team instanceof Team || $player->team_id === 'FA') {
            return null;
        }

        return NflGame::query()
            ->forTeam($player->team)
            ->forSeason($season)
            ->forWeek($week)
            ->first();
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
