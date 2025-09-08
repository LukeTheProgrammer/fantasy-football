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
    protected $signature = 'espn:nfl:get:team-rosters {espn_team_id?} {--A|all}';

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
        $teamId = $this->argument('espn_team_id');
        $getAll = $this->option('all');

        if ($teamId) {
            return $this->getRoster($teamId);
        }

        if ($getAll) {
            return $this->getAllRosters();
        }
    }

    protected function getRoster(int $teamId)
    {
        $nfl = Espn::nfl();

        $roster = $nfl->getTeamRoster($teamId);

        $this->saveRoster($teamId, $roster);

        return Command::SUCCESS;
    }

    protected function getAllRosters()
    {
        $nfl = Espn::nfl();

        Team::all()->each(function (Team $team) use ($nfl) {
            $roster = $nfl->getTeamRoster($team->espn_id);
            $this->saveRoster($team->espn_id, $roster);
        });
    }

    protected function saveRoster(int $teamId, array $roster)
    {
        $path = storage_path('data/espn/nfl/team-roster-' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($roster, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team Roster saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
