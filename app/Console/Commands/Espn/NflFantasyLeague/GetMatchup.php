<?php

namespace App\Console\Commands\Espn\NflFantasyLeague;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetMatchup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:ffl:matchup {league_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL Fantasy League matchup from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $leagueId = $this->argument('league_id');

        $nfl = Espn::nflFantasyLeague($leagueId);

        $matchup = $nfl->getMatchup();

        $path = storage_path('data/espn/ffl/' . $leagueId . '-matchup.json');

        $bytes = file_put_contents($path, json_encode($matchup, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Fantasy League Matchup saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
