<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;

class GetTeamRosters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:get:team-rosters
        { --a|all : Get all NFL teams }
        { espn_team_id? : The ESPN NFL team ID }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team roster from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            Team::all()->each(function (Team $team) {
                $this->getRoster($team->espn_id);
            });

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $this->getRoster($teamId);

            return Command::SUCCESS;
        }
    }

    protected function getRoster(int $teamId)
    {
        $roster = Espn::nfl()->getTeamRoster($teamId);

        $path = storage_path('data/espn/nfl/team-rosters/team-roster-' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($roster, JSON_PRETTY_PRINT));

        $this->info("NFL Team Roster saved to $path ($bytes bytes)");
    }
}
