<?php

namespace App\Console\Commands\Espn\Rosters;

use App\Facades\Action;
use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function Laravel\Prompts\confirm;

class LoadAllRosterFromFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:roster:load-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads all roster data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teams = Team::whereNotNull('espn_id')->get();

        $teams->each(function (Team $team) {
            $this->call('espn:roster:load', ['espn_team_id' => $team->espn_id]);
        });
    }
}
