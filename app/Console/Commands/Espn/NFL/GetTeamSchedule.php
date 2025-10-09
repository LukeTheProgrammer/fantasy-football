<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

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
        { season?         : Which season to pull   }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team schedule from the ESPN API.';

    protected ?Collection $teams = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->teams = Team::noFA()->get();

        $season = $this->argument('season') ?? select('Which season to pull', [2025, 2024]);

        if ($this->option('all')) {
            $this->teams->each(
                fn (Team $team) => $this->getSchedule($team->espn_id, $season)
            );

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $this->getSchedule($teamId, $season);

            return Command::SUCCESS;
        }

        $teamId = select('Which team to pull', $this->teams->pluck('name', 'espn_id')->toArray());

        $this->getSchedule($teamId, $season);

        return Command::SUCCESS;
    }

    public function getSchedule(int $teamId, int $season)
    {
        $nfl = Espn::nfl();

        if ($this->option('raw')) {
            $nfl->returnRaw = true;
        }

        $nfl->getTeamSchedule($teamId, $season);

        $this->info('NFL Team [' . $teamId . '] Schedule pulled and saved');
    }
}
