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
        { --espn  : Use ESPN; default is pro football reference }
        { season? : The season to import }
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
        $season = $this->argument('season') ?? select('Season', [2026, 2025, 2024], Season::current()->first()->id);

        Team::noFA()->get()->each(function (Team $team) use ($season) {
            $this->info('Importing ' . $season . ' Roster for ' . $team->id);

            if ($this->option('espn')) {
                Data::espn()->importNFLRosters($team, $season);
            } else {
                Data::pfr()->importNFLRosters($team, $season);
            }
        });
    }
}
