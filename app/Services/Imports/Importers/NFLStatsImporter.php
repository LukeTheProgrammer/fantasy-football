<?php

namespace App\Services\Imports\Importers;

use App\Enums\NFLPositions;
use App\Facades\Action;
use App\Facades\Player as PlayerFacade;
use App\Models\NflGame;
use App\Models\Player;
use App\Models\PlayerStatWeekly;
use App\Models\PlayerStatYearly;
use App\Models\Season;
use App\Services\Imports\Drivers\NFLStats\BaseNFLStatsDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Writes the player list, the schedule and player stat lines.
 *
 * The driver has already shaped every row for the tables, so nothing here knows
 * which source it came from. What it does know is how to turn a row's source
 * ids into this app's player and game, and what to do when it cannot.
 */
class NFLStatsImporter
{
    /**
     * The positions a fantasy league scores.
     *
     * Stat lines for everyone else are read and skipped: the file carries every
     * player who touched the field, and a linebacker's tackle count is not
     * something this app has any use for.
     *
     * @var array<int, string>
     */
    public const FANTASY_POSITIONS = [
        NFLPositions::QB->value,
        NFLPositions::RB->value,
        NFLPositions::FB->value,
        NFLPositions::WR->value,
        NFLPositions::TE->value,
        NFLPositions::K->value,
    ];

    /**
     * Columns the driver carries for resolving rows, which are not columns on
     * the stats tables.
     *
     * @var array<int, string>
     */
    private const RESOLUTION_KEYS = ['gsis_id', 'full_name', 'headshot', 'nflverse_game_id'];

    /**
     * How many rows are written before the batch is flushed.
     */
    private const CHUNK = 500;

    private int $written = 0;

    private int $skipped = 0;

    /**
     * @var array<int, array{reason: string, data: array}>
     */
    private array $errors = [];

    /**
     * Players already resolved this run, keyed by source id.
     *
     * @var array<string, int|null>
     */
    private array $players = [];

    public function __construct(public ?BaseNFLStatsDriver $driver = null)
    {
        //
    }

    /**
     * Build the player list from the source's own roster of everyone it knows.
     *
     * Only fantasy positions are created, and only players who were active in
     * or after the earliest season being imported — the file reaches back to
     * 1920, and a 1950s halfback is not a player this app has a use for.
     *
     * @return array{written: int, skipped: int, errors: array}
     */
    public function importPlayers(int $since): array
    {
        foreach ($this->driver->players() as $row) {
            if (!$this->isFantasyPosition(Arr::get($row, 'position_id'))) {
                continue;
            }

            if ((int) Arr::get($row, 'last_season') < $since) {
                continue;
            }

            $this->savePlayer($row);
        }

        return $this->result();
    }

    /**
     * Add the games the schedule is missing and stamp the ids other sources use
     * onto the games it already has.
     *
     * @return array{written: int, skipped: int, errors: array}
     */
    public function importGames(?int $season = null): array
    {
        $seasons = [];

        foreach ($this->driver->games($season) as $row) {
            // A game is proof its season happened, and seasons older than the
            // app has been running have no row of their own yet.
            $year = (int) Arr::get($row, 'season');

            if ($year > 0 && !isset($seasons[$year])) {
                Season::firstOrCreate(['id' => $year], ['is_current' => false]);
                $seasons[$year] = true;
            }

            $game = $this->findGame($row);

            $attributes = Arr::except($row, ['season_type']);

            if ($game instanceof NflGame) {
                // The row already here came from ESPN with a kickoff time this
                // source rounds, so only what is missing is filled in.
                $game->update(Arr::only($attributes, [
                    'nflverse_id', 'pfr_id', 'espn_id', 'is_playoff', 'home_score', 'away_score', 'is_completed',
                ]));
            } else {
                NflGame::create($attributes);
            }

            $this->written++;
        }

        return $this->result();
    }

    /**
     * Write one season of stat lines.
     *
     * @param string $window The source's own name for weekly rows or season totals.
     *
     * @return array{written: int, skipped: int, errors: array}
     */
    public function importStats(int $season, string $window, bool $weekly = true): array
    {
        $games = $weekly ? $this->gameIds($season) : [];
        $batch = [];

        foreach ($this->driver->stats($season, $window) as $row) {
            if (!$this->isFantasyPosition(Arr::get($row, 'position_id'))) {
                continue;
            }

            $prepared = $this->prepare($row, $games, $weekly);

            if ($prepared === null) {
                continue;
            }

            $batch[] = $prepared;

            if (count($batch) >= self::CHUNK) {
                $this->flush($batch, $weekly);
                $batch = [];
            }
        }

        $this->flush($batch, $weekly);

        return $this->result();
    }

    /**
     * Turn one row into a writable stat line, or null when it has no player.
     *
     * @param array<string, mixed> $row
     * @param array<string, int> $games
     *
     * @return array<string, mixed>|null
     */
    private function prepare(array $row, array $games, bool $weekly): ?array
    {
        $playerId = $this->resolvePlayer($row);

        if ($playerId === null) {
            $this->skipped++;

            return null;
        }

        $prepared = Arr::except($row, self::RESOLUTION_KEYS);
        $prepared['player_id'] = $playerId;
        $prepared['season_type'] = Arr::get($row, 'season_type')?->value;

        if ($weekly) {
            $prepared['nflverse_game_id'] = Arr::get($row, 'nflverse_game_id');
            $prepared['nfl_game_id'] = $games[Arr::get($row, 'nflverse_game_id')] ?? null;
        }

        return $prepared;
    }

    /**
     * @param array<int, array<string, mixed>> $batch
     */
    private function flush(array $batch, bool $weekly): void
    {
        if (empty($batch)) {
            return;
        }

        $model = $weekly ? PlayerStatWeekly::class : PlayerStatYearly::class;

        $this->written += Action::model($model)->upsert($batch);
    }

    /**
     * The player a stat line belongs to.
     *
     * A stat line is not the place to learn about a new player — the player
     * list import is — so a miss here is recorded and skipped rather than
     * quietly creating half a player from a box score.
     *
     * @param array<string, mixed> $row
     */
    private function resolvePlayer(array $row): ?int
    {
        $gsisId = Arr::get($row, 'gsis_id');

        if ($gsisId === null) {
            return null;
        }

        if (array_key_exists($gsisId, $this->players)) {
            return $this->players[$gsisId];
        }

        // Deliberately not a name lookup. Three different men called Josh
        // Johnson took a snap in 2021, and a stat line matched by name puts one
        // man's season on another's record.
        $player = Player::gsisId($gsisId)->first();

        if (!$player instanceof Player) {
            $this->addError('Player not found', Arr::only($row, ['gsis_id', 'full_name', 'position_id', 'team_id']));
        }

        return $this->players[$gsisId] = $player?->id;
    }

    /**
     * Create or update one player from the source's player list.
     *
     * @param array<string, mixed> $row
     */
    private function savePlayer(array $row): void
    {
        $data = Arr::except($row, ['rookie_season', 'last_season', 'status']);

        $player = $this->matchPlayer($row);

        if ($player instanceof Player) {
            $player->update($this->withoutConflicts($data, $player));
            $this->written++;

            return;
        }

        Player::create($data);
        $this->written++;
    }

    /**
     * The player row this source row describes, if the app already has one.
     *
     * A name is only allowed to decide the match when the candidate it finds
     * has no NFL id of its own yet. Once a row is claimed by one man's id, a
     * namesake arriving later is a different man and gets his own row.
     *
     * @param array<string, mixed> $row
     */
    private function matchPlayer(array $row): ?Player
    {
        $gsisId = Arr::get($row, 'gsis_id');

        if ($gsisId !== null && $player = Player::gsisId($gsisId)->first()) {
            return $player;
        }

        // The ids the app already holds are what link a known player to his
        // NFL id for the first time.
        foreach (['espn_id' => 'espnId', 'pfr_id' => 'pfrId'] as $key => $scope) {
            $value = Arr::get($row, $key);

            if ($value === null) {
                continue;
            }

            $candidate = Player::{$scope}($value)->first();

            if ($candidate instanceof Player && $this->isUnclaimed($candidate, $gsisId)) {
                return $candidate;
            }
        }

        $candidate = PlayerFacade::find([
            'full_name'   => Arr::get($row, 'full_name'),
            'position_id' => Arr::get($row, 'position_id'),
            'team_id'     => Arr::get($row, 'team_id'),
        ], ['record_missing' => false]);

        return $candidate instanceof Player && $this->isUnclaimed($candidate, $gsisId)
            ? $candidate
            : null;
    }

    /**
     * Whether a candidate is free to be this row's player, or already belongs
     * to someone else.
     */
    private function isUnclaimed(Player $candidate, ?string $gsisId): bool
    {
        return $candidate->gsis_id === null || $candidate->gsis_id === $gsisId;
    }

    /**
     * Drop any source id that another player already holds.
     *
     * The id columns are unique, and two sources disagreeing about which player
     * an id belongs to is a matching problem to look at rather than a write to
     * force through.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function withoutConflicts(array $data, Player $player): array
    {
        foreach (['gsis_id', 'espn_id', 'pfr_id'] as $column) {
            $value = Arr::get($data, $column);

            if ($value === null) {
                unset($data[$column]);

                continue;
            }

            $taken = Player::where($column, $value)
                ->where('id', '!=', $player->id)
                ->exists();

            if ($taken) {
                $this->addError('Conflicting ' . $column, [
                    'player_id' => $player->id,
                    $column     => $value,
                ]);

                unset($data[$column]);
            }
        }

        return $data;
    }

    /**
     * This season's games keyed by the id the stat rows refer to them by.
     *
     * @return array<string, int>
     */
    private function gameIds(int $season): array
    {
        return NflGame::query()
            ->forSeason($season)
            ->whereNotNull('nflverse_id')
            ->pluck('id', 'nflverse_id')
            ->all();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function findGame(array $row): ?NflGame
    {
        return NflGame::query()
            ->where('nflverse_id', Arr::get($row, 'nflverse_id'))
            ->orWhere(fn ($query) => $query
                ->whereNotNull('espn_id')
                ->where('espn_id', Arr::get($row, 'espn_id')))
            ->first();
    }

    private function isFantasyPosition(?string $position): bool
    {
        return in_array(strtoupper((string) $position), self::FANTASY_POSITIONS, true);
    }

    private function addError(string $reason, array $data): void
    {
        $this->errors[] = ['reason' => $reason, 'data' => $data];
    }

    /**
     * @return array{written: int, skipped: int, errors: array}
     */
    private function result(): array
    {
        return [
            'written' => $this->written,
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
