<?php

namespace App\Console\Commands\Espn\FantasyNFL;

use App\Facades\Espn;
use App\Models\League;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;

class GetMatchup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:ffl:get:matchup
        {league_id? : ESPN League ID}
        {--raw      : Return raw data instead of parsed objects}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL Fantasy League matchup from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $leagueId = $this->argument('league_id');

        if (! $leagueId) {
            $leagueId = select('League ID', League::all()->pluck('name', 'id')->toArray());
        }

        $league = League::findOrFail($leagueId);

        $fantasyNFL = Espn::fantasyNFL($league->credentials);

        if ($this->option('raw')) {
            $fantasyNFL->returnRaw = true;
        }

        $matchup = $fantasyNFL->getMatchup($league->platform_id);

        $parts = [
            'data',
            'espn',
            'ffl',
        ];

        if ($this->option('raw')) {
            $parts[] = 'raw';
        }

        $path = storage_path(implode('/', $parts) . '/' . $league->platform_id . '-matchup.json');

        $bytes = file_put_contents($path, json_encode($matchup, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Fantasy League Matchup saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
