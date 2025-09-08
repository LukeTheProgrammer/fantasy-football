<?php

namespace App\Console\Commands\Espn\Rosters;

use App\Models\Team;
use Illuminate\Console\Command;

class GetAllRosters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:roster:get-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads rosters from a ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teams = Team::whereNotNull('espn_id')->get();

        $teams->each(function ($team) {
            $this->call('espn:roster:get', ['espn_team_id' => $team->espn_id]);
        });
    }
}
