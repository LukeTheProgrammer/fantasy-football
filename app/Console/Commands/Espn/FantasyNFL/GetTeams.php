<?php

namespace App\Console\Commands\Espn\FantasyNFL;

use App\Facades\Espn;
use Illuminate\Console\Command;

class GetTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:ffl:get:teams
        {league_id? : ESPN League ID}
        {--s2=      : ESPN S2 token}
        {--swid=    : ESPN SWID token}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL Fantasy League teams from the ESPN API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $leagueId = $this->argument('league_id') ?? config('services.espn.default_league_id');

        $fantasyNFL = Espn::fantasyNFL([
            'leagueId' => $leagueId,
            's2'       => $this->option('s2') ?? config('services.espn.default_s2'),
            'swid'     => $this->option('swid') ?? config('services.espn.default_swid'),
        ]);

        $teams = $fantasyNFL->getTeams();

        $path = storage_path('data/espn/ffl/' . $leagueId . '-teams.json');

        $bytes = file_put_contents($path, json_encode($teams, JSON_PRETTY_PRINT));

        $this->info(PHP_EOL . "NFL Fantasy League Teams saved to $path ($bytes bytes)" . PHP_EOL);
    }
}
