<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetLeaders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:leaders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL leaders from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nfl = Espn::nfl();

        $leaders = $nfl->getLeaders();

        $path = storage_path('data/espn/nfl/leaders.json');

        $bytes = file_put_contents($path, json_encode($leaders, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Leaders saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
