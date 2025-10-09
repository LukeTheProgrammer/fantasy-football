<?php

namespace App\Console\Commands;

use App\Models\Week;
use Illuminate\Console\Command;

class ResetAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets all the data in the database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $season = date('Y');

        $this->call('migrate:fresh');
        $this->call('db:seed', ['-vvv' => true]);

        $this->info('Importing Fantasy League');
        $this->call('import:fantasy:league');

        $this->info('Importing Fantasy Roster');
        $this->call('import:fantasy:roster', ['leagueId' => 1, 'season' => $season]);

        $this->info('Importing NFL Projections');
        Week::forSeason($season)->get()->each(function ($week) {
            $this->info("Importing NFL Projections for Week {$week->week}");
            $this->call('import:nfl:projections', ['season' => $week->season_id, 'week' => $week->week]);

            if ($week->is_current) {
                return false;
            }
        });

        return Command::SUCCESS;
    }
}
