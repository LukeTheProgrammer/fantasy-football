<?php

namespace App\Console\Commands\Data\Imports\Fantasy;

use App\Facades\Import;
use App\Models\League;
use App\Models\LeagueMemberRoster;
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
        { year?     : Year to pull   }
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

        $year = $this->argument('year') ?? select('Select a year', [2025, 2024], 2025);

        $league = League::findOrFail($leagueId);

        $importer = Import::fantasyNFL($league->platform);

        $this->info('Importing rosters...');

        $importer->importRosters($league, $year);

        $this->info('Rosters imported successfully!');
    }
}
