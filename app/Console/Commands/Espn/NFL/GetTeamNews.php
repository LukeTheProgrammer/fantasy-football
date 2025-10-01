<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use App\Models\Team;
use Illuminate\Console\Command;

class GetTeamNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:get:team-news
        { --a|all : Get all NFL teams }
        { espn_team_id? : The ESPN NFL team ID }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads team news from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            Team::noFA()->get()->each(
                fn (Team $team) => $this->getTeamNews($team->espn_id)
            );

            return Command::SUCCESS;
        }

        $teamId = $this->argument('espn_team_id');

        if ($teamId) {
            $this->getTeamNews($teamId);

            return Command::SUCCESS;
        }
    }

    public function getTeamNews(int $teamId)
    {
        $nfl = Espn::nfl();

        $news = $nfl->getTeamNews($teamId);

        $path = storage_path('data/espn/nfl/team-news/team-news-' . $teamId . '.json');

        $bytes = file_put_contents($path, json_encode($news, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team News saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
