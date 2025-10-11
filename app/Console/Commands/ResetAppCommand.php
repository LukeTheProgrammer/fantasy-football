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
    protected $signature = 'app:reset
        { season? : Season }';

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
        $season = $this->argument('season') ?? date('Y');

        $this->call('migrate:fresh');
        $this->call('db:seed', ['-vvv' => true]);

        $this->info('Importing Fantasy League');
        $this->call('import:fantasy:league');

        $this->info('Importing Fantasy Roster');
        $this->call('import:fantasy:roster', ['leagueId' => 1, 'season' => $season]);

        $this->info('Importing NFL Projections');
        $this->call('import:fantasy:projections', ['season' => $season]);

        return Command::SUCCESS;
    }
}
