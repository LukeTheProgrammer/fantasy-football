<?php

namespace App\Console\Commands\Imports\FantasyNFL;

use App\Enums\FantasyPlatformsEnum;
use App\Facades\Espn;
use App\Models\Player;
use App\Models\FantasyPointsWeek;
use App\Models\League;
use App\Models\NflGame;
use App\Services\Espn\Data\FantasyNFL\PlayerData;
use App\Services\Espn\Data\FantasyNFL\PlayerStatsData;
use App\Services\Espn\Data\FantasyNFL\ResourceTeamsData;
use App\Services\Espn\Data\FantasyNFL\TeamRosterEntryData;
use App\Services\Espn\Resources\FantasyNFL;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class ImportFantasyPointsFromFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fantasy-nfl:file-points
        { year? : Year for which to import points }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy NFL Points from a file';

    protected FantasyNFL $api;

    protected League $league;

    protected Collection $games;

    protected ?int $year = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setUp();

        $this->info('Importing Fantasy Points from file');

        $this->import();

        $this->info('Fantasy Points imported successfully: ' . $this->league->name);
    }

    protected function setUp()
    {
        $this->year = $this->argument('year') ?? select('Select a year', [2025, 2024], 2025);

        $this->games = NflGame::forYear($this->year)->get()->keyBy('espn_id');

        $leagueId = select(
            label: 'League',
            options: League::all()->pluck('name', 'id')->toArray(),
            default: null,
        );

        $this->league = League::findOrFail($leagueId);
    }

    protected function import()
    {
        for ($week = 1; $week <= 17; $week++) {
            $this->league->members->each(function ($member) use ($week) {
                $this->info('Loading Points for ' . $member->team_name . ' Week ' . $this->year . '.' . $week);
                $this->loadPointsFile($week, $member->external_id);
            });
        }
    }

    private function loadPointsFile(int $week, int|string $memberId)
    {
        $path = $this->getPath($this->league->platform_id, $memberId, $this->year, $week);

        $this->info('Path: ' . $path);

        if (! file_exists($path)) {
            $this->error('File does not exist: ' . $path);
            return;
        }

        $points = json_decode(file_get_contents($path), true);

        $this->processPoints($points);
    }

    private function getPath(int|string $leagueId, int|string $memberId, int $year, int $week): string
    {
        $base = 'data/espn/ffl/rosters/formatted/';
        $parts = [$leagueId, 'team', $memberId, $year, 'week', $week];

        return storage_path($base . '/' . implode('-', $parts) . '.json');
    }

    private function processPoints(array $points)
    {
        foreach ($points as $playerId => $data) {
            $player = Player::espnId($playerId)->first();

            if (! $player instanceof Player) {
                continue;
            }

            foreach ($data as $point) {
                $this->processStat($player, $point);
            }
        }
    }

    protected function processStat(Player $player, array $point)
    {
        $gameId  = intval(Arr::get($point, 'game_id', false));
        $year    = intval(Arr::get($point, 'season', false));
        $week    = intval(Arr::get($point, 'week', false));
        $points  = floatVal(Arr::get($point, 'points', 0));

        $isProjection = Arr::get($point, 'is_projected', false);

        if ($gameId == $year) {
            // Season total value
            return;
        }

        if ($gameId == $year . $week && $isProjection) {
            $game = NflGame::forYear($year)
                ->forWeek($week)
                ->where(function ($q) use ($player) {
                    $q->orWhere('home_team_id', $player->team_id)
                        ->orWhere('away_team_id', $player->team_id);
                })
                ->first();

            if (! $game instanceof NflGame) {
                return;
            }

            FantasyPointsWeek::updateOrCreate(
                [
                    'nfl_game_id' => $game->id,
                    'league_id'   => $this->league->id,
                    'player_id'   => $player->id,
                ],
                [
                    'espn_projected_points' => $points,
                ],
            );

            return;
        }

        $game = $this->games->get($gameId);

        if (! $game instanceof NflGame) {
            return;
        }

        FantasyPointsWeek::updateOrCreate(
            [
                'nfl_game_id' => $game->id,
                'league_id'   => $this->league->id,
                'player_id'   => $player->id,
            ],
            [
                'points' => $points,
            ],
        );
    }
}
