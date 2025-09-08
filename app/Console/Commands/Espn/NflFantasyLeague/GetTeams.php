<?php

namespace App\Console\Commands\Espn\NflFantasyLeague;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:ffl:teams {league_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL Fantasy League teams from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $leagueId = $this->argument('league_id');

        $nfl = Espn::nflFantasyLeague($leagueId);

        $teams = $nfl->getTeams();

        $path = storage_path('data/espn/ffl/' . $leagueId . '-teams.json');

        $bytes = file_put_contents($path, json_encode($teams, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Fantasy League Teams saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
