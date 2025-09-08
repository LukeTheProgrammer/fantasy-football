<?php

namespace App\Console\Commands\Espn\NflTeam;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetDepthChart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl-team:get:depth-chart {team_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL team from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teamId = $this->argument('team_id');

        $nfl = Espn::nflTeam($teamId);

        $depthChart = $nfl->getDepthChart();

        $path = storage_path('data/espn/nfl-team/' . $teamId . '-depth-chart.json');

        $bytes = file_put_contents($path, json_encode($depthChart, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team Depth Chart saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
