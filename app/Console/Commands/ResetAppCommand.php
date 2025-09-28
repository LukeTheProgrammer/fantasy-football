<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

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
        $this->call('migrate:fresh');
        $this->call('db:seed', ['-vvv' => true]);
        $this->call('import:fantasy:league');
        $this->call('import:fantasy:roster', ['leagueId' => 1, 'year' => 2025]);

        return Command::SUCCESS;
    }
}
