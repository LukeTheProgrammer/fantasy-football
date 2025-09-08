<?php

namespace App\Console\Commands\Espn\Rosters;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetRoster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:roster:get {espn_team_id}';

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
        $espnTeamId = $this->argument('espn_team_id');

        $data = Espn::getRoster($espnTeamId);

        $path = database_path('data/ESPN/rosters/espn-team-' . $espnTeamId . '-roster.json');

        $bytes = file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

        $this->info("Roster data saved to $path ($bytes bytes)");
    }
}
