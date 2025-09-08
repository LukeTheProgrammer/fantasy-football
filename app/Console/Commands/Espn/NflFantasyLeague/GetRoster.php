<?php

namespace App\Console\Commands\Espn\NflFantasyLeague;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetRoster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:ffl:roster {league_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL Fantasy League roster from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $leagueId = $this->argument('league_id');

        $nfl = Espn::nflFantasyLeague($leagueId);

        $roster = $nfl->getRoster();

        $path = storage_path('data/espn/ffl/' . $leagueId . '-roster.json');

        $bytes = file_put_contents($path, json_encode($roster, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Fantasy League Roster saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
