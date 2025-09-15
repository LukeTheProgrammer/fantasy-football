<?php

namespace App\Console\Commands\Imports\FantasyNFL;

use App\Enums\FantasyPlatformsEnum;
use App\Facades\Espn;
use App\Models\Player;
use App\Models\FantasyPointsWeek;
use App\Models\League;
use App\Models\User;
use App\Services\Espn\Data\FantasyNFL\PlayerData;
use App\Services\Espn\Data\FantasyNFL\PlayerStatsData;
use App\Services\Espn\Data\FantasyNFL\ResourceTeamsData;
use App\Services\Espn\Data\FantasyNFL\TeamRosterEntryData;
use App\Services\Espn\Resources\FantasyNFL;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class ImportProjectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fantasy-nfl:projections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy NFL Projections';

    protected FantasyNFL $api;

    protected User $user;

    protected League $league;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setUp();

        $this->info('Importing Fantasy Projections');

        $this->import();

        $this->info('Fantasy Projections imported successfully: ' . $this->league->name);
    }

    protected function setUp()
    {
        $userId = select('User', User::all()->pluck('name', 'id')->toArray());

        $this->user = User::findOrFail($userId);

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
        $this->info('Importing Projections for ' . $team->name);

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

        /** @var PlayerStatsData $projectionStat */
        $projectionStat = $player->getProjectedWeekPoints();

        if (! $projectionStat instanceof PlayerStatsData) {
            return true;
        }

        FantasyPointsWeek::updateOrCreate(
            [
                'league_id' => $this->league->id,
                'player_id' => $playerModel->id,
                'year' => $projectionStat->seasonId,
                'week_number' => $projectionStat->scoringPeriodId,
            ],
            ['espn_projected_points' => $projectionStat->appliedTotal],
        );
    }
}
