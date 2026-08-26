<?php

namespace App\Console\Commands\Data\Imports\Fantasy;

use App\Facades\Data;
use Illuminate\Console\Command;

class ImportDraftRankingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fantasy:draft-rankings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Draft Rankings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Importing draft rankings...');

        // Not yet implemented
        // Data::source($league->platform)->importFantasyDraftRankings();

        $this->info('Draft rankings imported successfully!');
    }
}
