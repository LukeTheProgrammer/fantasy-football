<?php

namespace App\Console\Commands\Espn\Players;

use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoadAllTeamPlayersFromFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:load:all-team-players';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads player data from a file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('players')->truncate();
        Schema::enableForeignKeyConstraints();

        $teams = Team::all();

        $c = $teams->count();

        $teams->each(function ($team, $i) use ($c) {
            $this->info('Loading All Players for ' . $team->location . ' ' . $team->name . ' (' . $i . ' of ' . $c . ')');
            $this->call('espn:load:team-players:file', ['espn_team_id' => $team->espn_id]);
        });
    }
}
