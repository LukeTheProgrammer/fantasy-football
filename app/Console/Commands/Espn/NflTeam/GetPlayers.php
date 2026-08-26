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
        $this->nfl = Espn::nflTeam();

        if ($this->option('all')) {
            Team::noFA()->get()->each(function ($team) {
                $this->info("Getting NFL Team Players for team $team->espn_id");
                $this->getPlayers($team);
            });

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $team = Team::forEspnId($teamId)->first();

            if (!$team instanceof Team) {
                $this->error("Team not found: $teamId");

                return Command::FAILURE;
            }

            $this->info("Getting NFL Team Players for team $teamId");
            $this->getPlayers($team);

            return Command::SUCCESS;
        }
    }

    protected function getPlayers(Team $team, int $page = 1)
    {
        $players = $this->nfl->getPlayers($team, $page);

        $path = storage_path('data/espn/nfl-teams/players/' . $team->espn_id . '-page-' . $page . '.json');

        $bytes = file_put_contents($path, json_encode($players, JSON_PRETTY_PRINT));

        $this->info("NFL Team Players saved to $path ($bytes bytes)");

        if ($players['pageCount'] > $page) {
            $this->getPlayers($team, $page + 1);
        }
    }
}
