<?php

namespace App\Console\Commands\Espn;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetTeam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:team:get {team_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads team data from a ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teamId = $this->argument('team_id');

        $data = Espn::getTeam($teamId);

        $path = database_path('data/espn-team-' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

        $this->info("Team data saved to $path ($bytes bytes)");
    }
}
