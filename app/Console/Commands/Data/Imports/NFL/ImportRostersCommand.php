<?php

namespace App\Console\Commands\Data\Imports\NFL;

use App\Facades\Data;
use App\Models\Team;
use App\Models\Season;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;

class ImportRostersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:nfl:roster
        { year? : The year to import }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import NFL Roster';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?? select('Year', [2025, 2024], Season::current()->first()->id);

        Team::noFA()->get()->each(function (Team $team) use ($year) {
            $this->info('Importing ' . $year . ' Roster for ' . $team->id);
            Data::espn()->importNFLRosters($team, $year);
        });
    }
}
