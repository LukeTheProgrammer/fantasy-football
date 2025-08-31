<?php

namespace App\Console\Commands\Espn\Teams;

use App\Models\Team;
use Illuminate\Console\Command;

class GetAllTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:team:get-all';

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
        $teams = Team::whereNotNull('espn_id')->get();

        $teams->each(function (Team $team) {
            $this->call('espn:team:get', ['team_id' => $team->espn_id]);
        });
    }
}
