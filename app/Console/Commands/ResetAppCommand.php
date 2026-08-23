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

        $this->warn('This drops EVERY table, players and player aliases included.');
        $this->warn('Aliases are only restored from database/data/player_aliases.json.');

        if (! $this->confirm('Run data:dump:players first? Answer no only if the dump is already current.', true)) {
            if (! $this->confirm('Continue without a fresh dump and risk losing alias work?', false)) {
                return Command::FAILURE;
            }
        } else {
            $this->call('data:dump:players');
        }

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
