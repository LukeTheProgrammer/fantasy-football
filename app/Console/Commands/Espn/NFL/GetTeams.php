<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:get:teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL teams from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nfl = Espn::nfl();

        $teams = $nfl->getTeams();

        $path = storage_path('data/espn/nfl/teams.json');

        $bytes = file_put_contents($path, json_encode($teams, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Teams saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
