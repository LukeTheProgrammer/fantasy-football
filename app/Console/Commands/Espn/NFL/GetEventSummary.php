<?php

namespace App\Console\Commands\Espn\NFL;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetEventSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:nfl:get:event-summary {event_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL event summary from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventId = $this->argument('event_id');

        $nfl = Espn::nfl();

        $news = $nfl->getEventSummary($eventId);

        $path = storage_path('data/espn/nfl/event-summary-' . $eventId . '.json');

        $bytes = file_put_contents($path, json_encode($news, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Event Summary saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
