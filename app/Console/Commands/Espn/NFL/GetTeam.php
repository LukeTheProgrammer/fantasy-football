<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class GetTeam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:get:team {espn_team_id?} {--A|all}';

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
        $teamId = $this->argument('espn_team_id');
        $getAll = $this->option('all');

        if ($teamId) {
            return $this->getTeam($teamId);
        }

        if ($getAll) {
            return $this->getAllTeams();
        }
    }

    protected function getTeam(int $teamId)
    {
        $team = Espn::nfl()->getTeam($teamId);

        $path = storage_path('data/espn/nfl/team-' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($team, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team saved to $path ($bytes bytes)" . PHP_EOL);
    }

    protected function getAllTeams()
    {
        $teamList = Espn::nfl()->getTeams();

        $teams = Arr::get($teamList, 'sports.0.leagues.0.teams', []);

        foreach ($teams as $team) {
            $this->getTeam(Arr::get($team, 'team.id'));
        }
    }
}
