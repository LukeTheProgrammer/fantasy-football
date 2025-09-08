<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetTeamSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:team-schedule {team_id}';

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
        $teamId = $this->argument('team_id');

        $nfl = Espn::nfl();

        $schedule = $nfl->getTeamSchedule($teamId);

        $path = storage_path('data/espn/nfl/team-schedule-' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($schedule, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team Schedule saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
