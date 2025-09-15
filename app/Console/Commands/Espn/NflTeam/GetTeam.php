<?php

namespace App\Console\Commands\Espn\NflTeam;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;

class GetTeam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl-team:get:team
        { --a|all : Get all NFL teams }
        { espn_team_id? : The ESPN NFL team ID }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            Team::all()->each(function ($team) {
                $this->getTeam($team->espn_id);
            });

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $this->getTeam($teamId);

            return Command::SUCCESS;
        }
    }

    public function getTeam(int $teamId)
    {
        $nfl = Espn::nflTeam($teamId);

        $team = $nfl->getTeam();

        $path = storage_path('data/espn/nfl-teams/teams/' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($team, JSON_PRETTY_PRINT));

        $this->info("NFL Team saved to $path ($bytes bytes)");
    }
}
