<?php

namespace App\Console\Commands\Espn\NflTeam;

use App\Facades\Espn;
use App\Services\Espn\Resources\NflTeam;
use Illuminate\Console\Command;

class GetPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl-team:get:players {team_id}';

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
        $teamId = $this->argument('team_id');

        $this->nfl = Espn::nflTeam($teamId);

        $this->getPlayers();
    }

    protected function getPlayers(int $page = 1)
    {
        $players = $this->nfl->getPlayers($page);

        $path = storage_path('data/espn/nfl-team/' . $this->nfl->teamId . '-players-' . $page . '.json');

        $bytes = file_put_contents($path, json_encode($players, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team Players saved to $path ($bytes bytes)" . PHP_EOL);

        if ($players['pageCount'] > $page) {
            $this->getPlayers($page + 1);
        }
    }
}
