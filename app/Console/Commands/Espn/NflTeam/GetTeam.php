<?php

namespace App\Console\Commands\Espn\NflTeam;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetTeam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl-team:get:team {team_id}';

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
        $teamId = $this->argument('team_id');

        $nfl = Espn::nflTeam($teamId);

        $team = $nfl->getTeam();

        $path = storage_path('data/espn/nfl-team/' . $teamId . '-team.json');

        $bytes = file_put_contents($path, json_encode($team, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
