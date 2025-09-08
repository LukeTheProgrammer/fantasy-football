<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetTeamRoster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:team-roster {team_id}';

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
        $teamId = $this->argument('team_id');

        $nfl = Espn::nfl();

        $roster = $nfl->getTeamRoster($teamId);

        $path = storage_path('data/espn/nfl/team-roster-' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($roster, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team Roster saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
