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
            Team::noFA()->get()->each(
                fn (Team $team) => $this->getEvents($team)
            );

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $team = Team::forEspnId($teamId)->first();

            if (! $team instanceof Team) {
                $this->error("Team not found: $teamId");

                return Command::FAILURE;
            }

            $this->getEvents($team);

            return Command::SUCCESS;
        }
    }

    public function getEvents(Team $team)
    {
        $nfl = Espn::nflTeam();

        $events = $nfl->getEvents($team);

        $path = storage_path('data/espn/nfl-teams/events/' . $team->espn_id . '.json');

        $bytes = file_put_contents($path, json_encode($events, JSON_PRETTY_PRINT));

        $this->info("NFL Team Events saved to $path ($bytes bytes)");
    }
}
