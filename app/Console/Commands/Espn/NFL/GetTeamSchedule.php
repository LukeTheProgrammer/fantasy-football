<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;

class GetTeamSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:get:team-schedule
        { --a|all : Get all NFL teams }
        { --r|raw : Return raw response }
        { espn_team_id? : The ESPN NFL team ID }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team schedule from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            Team::all()->each(function ($team) {
                $this->getSchedule($team->espn_id);
            });
            return;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $this->getSchedule($teamId);
            return;
        }
    }

    public function getSchedule(int $teamId)
    {
        $nfl = Espn::nfl();

        $basePath = storage_path('data/espn/nfl/team-schedules');

        if ($this->option('raw')) {
            $nfl->returnRaw = true;
            $basePath .= '/raw';
        }

        $schedule = $nfl->getTeamSchedule($teamId);

        $path = $basePath . '/team-schedule-' . $teamId . '.json';

        $bytes = file_put_contents($path, json_encode($schedule, JSON_PRETTY_PRINT));

        $this->info("NFL Team Schedule saved to $path ($bytes bytes)");
    }
}
