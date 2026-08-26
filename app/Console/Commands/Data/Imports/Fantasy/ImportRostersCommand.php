<?php

namespace App\Console\Commands\Data\Imports\Fantasy;

use App\Facades\Data;
use App\Models\League;
use Illuminate\Console\Command;

use function Laravel\Prompts\select;

class ImportRostersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fantasy:roster
        { leagueId? : League to pull }
        { season?   : Season to pull }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy NFL Roster';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $leagueId = $this->argument('leagueId') ?? select('Select a league', League::all()->pluck('name', 'id')->toArray());

        $season = $this->argument('season') ?? select('Select a season', [2025, 2024], 2025);

        $league = League::findOrFail($leagueId);

        $this->info('Importing rosters...');

        Data::source($league->platform)->importFantasyRosters($league, $season);

        $this->info('Rosters imported successfully!');
    }
}
