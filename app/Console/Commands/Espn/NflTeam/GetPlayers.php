<?php

namespace App\Console\Commands\Espn\NflTeam;

use App\Facades\Espn;
use App\Models\Team;
use App\Services\Espn\Resources\NflTeam;
use Illuminate\Console\Command;

class GetPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl-team:get:players
        { --a|all : Get all players for all teams }
        { espn_team_id? : The ESPN NFL team ID }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team Players from the ESPN API.';

    protected ?NflTeam $nfl = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            Team::noFA()->get()->each(function ($team) {
                $this->info("Getting NFL Team Players for team $team->espn_id");
                $this->getPlayers($team->espn_id);
            });

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $this->info("Getting NFL Team Players for team $teamId");
            $this->getPlayers($teamId);

            return Command::SUCCESS;
        }

    }

    protected function getPlayers(int $teamId, int $page = 1)
    {
        $this->nfl = Espn::nflTeam($teamId);

        $players = $this->nfl->getPlayers($page);

        $path = storage_path('data/espn/nfl-teams/players/' . $this->nfl->teamId . '-page-' . $page . '.json');

        $bytes = file_put_contents($path, json_encode($players, JSON_PRETTY_PRINT));

        $this->info("NFL Team Players saved to $path ($bytes bytes)");

        if ($players['pageCount'] > $page) {
            $this->getPlayers($teamId, $page + 1);
        }
    }
}
