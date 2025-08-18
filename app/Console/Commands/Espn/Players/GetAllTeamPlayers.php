<?php

namespace App\Console\Commands\Espn\Players;

use App\Models\Team;
use App\Facades\Espn;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class GetAllTeamPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:team-players:get-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads player data from the ESPN API.';

    /**
     * Array of player data.
     *
     * @var array
     */
    protected array $players = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teams = Team::all();

        $teams->each(function ($team) {
            $this->call('espn:team-players:get', ['team_id' => $team->espn_id]);
        });
    }
}
