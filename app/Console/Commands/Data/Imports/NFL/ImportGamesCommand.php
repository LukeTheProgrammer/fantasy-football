<?php

namespace App\Console\Commands\Data\Imports\NFL;

use App\Enums\Datum;
use App\Facades\Import;
use App\Models\Season;
use App\Traits\ReportsImportErrors;
use Illuminate\Console\Command;

class ImportGamesCommand extends Command
{
    use ReportsImportErrors;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:nfl:games
        { season?  : The season to import, defaults to the current season }
        { --all    : Import every season the source holds }
        { --force  : Pull the file again instead of reading the archive }
        { --errors : List every unresolved row rather than a count }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the NFL schedule, including the postseason';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $season = $this->option('all')
            ? null
            : (int) ($this->argument('season') ?? Season::current()->first()?->id);

        $this->info('Importing ' . ($season ?? 'every') . ' season schedule');

        $importer = Import::nflStats(Datum::SOURCE_NFLVERSE, (bool) $this->option('force'));

        $result = $importer->importGames($season);

        $this->table(['Written', 'Skipped'], [[$result['written'], $result['skipped']]]);

        $this->reportErrors($importer, $result);

        return self::SUCCESS;
    }
}
