<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetTeamNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:get:team-news {team_id}';

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
        $teamId = $this->argument('team_id');

        $nfl = Espn::nfl();

        $news = $nfl->getTeamNews($teamId);

        $path = storage_path('data/espn/nfl/team-news.json');

        $bytes = file_put_contents($path, json_encode($news, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Team News saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
