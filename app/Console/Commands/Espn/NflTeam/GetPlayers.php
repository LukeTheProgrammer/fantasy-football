<?php

namespace App\Console\Commands\Espn\NflTeam;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl-team:players {team_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team Players from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teamId = $this->argument('team_id');

        $nfl = Espn::nflTeam($teamId);

        // TODO: Add pagination support
        $players = $nfl->getPlayers();

        $path = storage_path('data/espn/nfl-team/' . $teamId . '-players.json');

        $bytes = file_put_contents($path, json_encode($players, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team Players saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
