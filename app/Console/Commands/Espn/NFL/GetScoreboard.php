<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetScoreboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:scoreboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL scoreboard from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nfl = Espn::nfl();

        $news = $nfl->getScoreboard();

        $path = storage_path('data/espn/nfl/scoreboard.json');

        $bytes = file_put_contents($path, json_encode($news, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Scoreboard saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
