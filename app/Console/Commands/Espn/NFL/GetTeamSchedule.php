<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;

class GetTeamSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:get:team-schedule
        { --a|all       : Get all NFL teams    }
        { --r|raw       : Return raw response  }
        { espn_team_id? : The ESPN NFL team ID }
        { year?         : Which year to pull   }
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
        $year = $this->argument('year') ?? select('Which year to pull', [2025, 2024]);

        if ($this->option('all')) {
            Team::noFA()->get()->each(
                fn (Team $team) => $this->getSchedule($team->espn_id, $year)
            );

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $this->getSchedule($teamId, $year);
        } else {
            $teamId = select('Which team to pull', Team::noFA()->get()->pluck('name', 'espn_id')->toArray());
            $this->getSchedule($teamId, $year);
        }

        return Command::SUCCESS;
    }

    public function getSchedule(int $teamId, int $year)
    {
        $nfl = Espn::nfl();

        $basePath = storage_path('data/espn/nfl/team-schedules');

        if ($this->option('raw')) {
            $nfl->returnRaw = true;
            $basePath .= '/raw';
        } else {
            $basePath .= '/formatted';
        }

        $schedule = $nfl->getTeamSchedule($teamId, $year);

        $path = $basePath . '/team-schedule-' . $teamId . '-' . $year . '.json';

        $bytes = file_put_contents($path, json_encode($schedule, JSON_PRETTY_PRINT));

        $this->info("NFL Team Schedule saved to $path ($bytes bytes)");
    }
}
