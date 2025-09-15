<?php

namespace App\Console\Commands\Espn\NflTeam;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;

class GetEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl-team:get:events
        { --a|all : Get all NFL teams }
        { espn_team_id? : The ESPN NFL team ID }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team Events from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            Team::all()->each(function ($team) {
                $this->getEvents($team->espn_id);
            });

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $this->getEvents($teamId);

            return Command::SUCCESS;
        }
    }

    public function getEvents(int $teamId)
    {
        $nfl = Espn::nflTeam($teamId);

        $events = $nfl->getEvents();

        $path = storage_path('data/espn/nfl-teams/events/' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($events, JSON_PRETTY_PRINT));

        $this->info("NFL Team Events saved to $path ($bytes bytes)");
    }
}
