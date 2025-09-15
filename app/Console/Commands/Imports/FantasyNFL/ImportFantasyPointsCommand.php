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
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class ImportFantasyPointsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fantasy-nfl:points
        { year? : Year for which to import points }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy NFL Points';

    protected FantasyNFL $api;

    protected League $league;

    protected Collection $games;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setUp();

        $this->info('Importing Fantasy Points');

        $this->import();

        $this->info('Fantasy Points imported successfully: ' . $this->league->name);
    }

    protected function setUp()
    {
        $year = $this->argument('year') ?? date('Y');

        $this->games = NflGame::forYear($year)->get()->keyBy('espn_id');

        $leagueId = select(
            label: 'League',
            options: League::all()->pluck('name', 'id')->toArray(),
            default: null,
        );

        $this->league = League::findOrFail($leagueId);

        $platform = FantasyPlatformsEnum::from(Str::upper($this->league->platform));

        if ($platform === FantasyPlatformsEnum::ESPN) {
            return $this->setUpEspnImporter();
        }
    }

    protected function setUpEspnImporter()
    {
        $this->api = Espn::fantasyNFL($this->league->credentials);
    }

    protected function import()
    {
        /** @var ResourceLeagueData $data */
        $data = $this->api->getRosters();

        $data->teams->each(fn (ResourceTeamsData $team) => $this->processTeam($team));
    }

    protected function processTeam(ResourceTeamsData $team)
    {
        $this->info('Importing Points for ' . $team->name);

        /** @var TeamRosterData $roster */
        $roster = $team->roster;

        $roster->entries->each(fn (TeamRosterEntryData $entry) => $this->processEntry($entry));
    }

    protected function processEntry(TeamRosterEntryData $entry)
    {
        /** @var PlayerPoolEntryData $pool */
        $pool = $entry->playerPoolEntry;

        /** @var PlayerData $player */
        $player = $pool->player;

        $playerModel = Player::espnId($player->id)->first();

        if (! $playerModel instanceof Player) {
            return true;
        }

        $player->stats->each(fn ($stat) => $this->processStat($playerModel, $stat));
    }

    protected function processStat(Player $player, PlayerStatsData $stat)
    {
        $game = $this->games->get($stat->externalId);

        if (! $game instanceof NflGame) {
            return true;
        }

        $data = ($stat->isActual)
            ? [ 'points' => $stat->appliedTotal ]
            : [ 'espn_projected_points' => $stat->appliedTotal ];

        FantasyPointsWeek::updateOrCreate(
            [
                'nfl_game_id' => $game->id,
                'league_id'   => $this->league->id,
                'player_id'   => $player->id,
            ],
            $data,
        );
    }
}
