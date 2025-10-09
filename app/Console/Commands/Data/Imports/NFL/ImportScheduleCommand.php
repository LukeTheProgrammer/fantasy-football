<?php

namespace App\Console\Commands\Data\Imports\NFL;

use App\Facades\Data;
use App\Models\Team;
use Illuminate\Console\Command;

class ImportScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:nfl:schedule
        { season? : The season to import }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import NFL Schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $season = $this->argument('season') ?? date('Y');

        Team::noFA()->get()->each(function (Team $team) use ($season) {
            $this->info('Importing ' . $season . ' Schedule for ' . $team->id);
            Data::espn()->importNFLSchedule($team, $season);
        });
    }
}
