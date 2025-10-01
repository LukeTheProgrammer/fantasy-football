<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;

class GetTeam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:get:team
        { --a|all : Get all NFL teams }
        { espn_team_id? : The ESPN NFL team ID }
    ';

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
        if ($this->option('all')) {
            Team::noFA()->get()->each(
                fn (Team $team) => $this->getTeam($team->espn_id)
            );

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $this->getTeam($teamId);

            return Command::SUCCESS;
        }
    }

    protected function getTeam(int $teamId)
    {
        $team = Espn::nfl()->getTeam($teamId);

        $path = storage_path('data/espn/nfl/teams/team-' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($team, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
