<?php

namespace App\Console\Commands\Data\Imports\NFL;

use App\Enums\Datum;
use App\Facades\Import;
use App\Traits\ReportsImportErrors;
use Illuminate\Console\Command;

class ImportPlayersCommand extends Command
{
    use ReportsImportErrors;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:nfl:players
        { --since=2021 : Skip players whose last season is older than this }
        { --force      : Pull the file again instead of reading the archive }
        { --errors     : List every unresolved row rather than a count }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the NFL player list, with the ids every other source uses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $since = (int) $this->option('since');

        $this->info('Importing players active in ' . $since . ' or later');

        $importer = Import::nflStats(Datum::SOURCE_NFLVERSE, (bool) $this->option('force'));

        $result = $importer->importPlayers($since);

        $this->table(['Written', 'Skipped'], [[$result['written'], $result['skipped']]]);

        $this->reportErrors($importer, $result);

        return self::SUCCESS;
    }
}
