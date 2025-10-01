<?php

namespace App\Console\Commands\Espn\NflTeam;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;

class GetDepthChart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl-team:get:depth-chart
        { --a|all : Get all NFL teams }
        { espn_team_id? : The ESPN NFL team ID }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team depth chart from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            Team::noFA()->get()->each(
                fn (Team $team) => $this->getDepthChart($team->espn_id)
            );

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $this->getDepthChart($teamId);

            return Command::SUCCESS;
        }
    }

    public function getDepthChart(int $teamId)
    {
        $nfl = Espn::nflTeam($teamId);

        $depthChart = $nfl->getDepthChart();

        $path = storage_path('data/espn/nfl-teams/depth-charts/' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($depthChart, JSON_PRETTY_PRINT));

        $this->info("NFL Team Depth Chart saved to $path ($bytes bytes)");
    }
}
