<?php

namespace App\Console\Commands\Data\Imports\NFL;

use App\Enums\Datum;
use App\Facades\Import;
use App\Models\Season;
use App\Services\Nflverse\Resources\PlayerStatsResource;
use App\Traits\ReportsImportErrors;
use Illuminate\Console\Command;

class ImportStatsCommand extends Command
{
    use ReportsImportErrors;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:nfl:stats
        { season?  : The season to import, defaults to the current season }
        { --weekly : Import only the weekly lines }
        { --season : Import only the season totals }
        { --force  : Pull the files again instead of reading the archive }
        { --errors : List every unresolved row rather than a count }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import NFL player stats for a season, weekly and totalled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $season = (int) ($this->argument('season') ?? Season::current()->first()?->id);

        $importer = Import::nflStats(Datum::SOURCE_NFLVERSE, (bool) $this->option('force'));

        // Totals come from their own file rather than being summed out of the
        // weekly rows, so that the two can be checked against each other.
        if (!$this->option('weekly')) {
            $this->info('Importing ' . $season . ' season totals');

            foreach ([PlayerStatsResource::WINDOW_REGULAR, PlayerStatsResource::WINDOW_POST] as $window) {
                $result = $importer->importStats($season, $window, weekly: false);
            }

            $this->table(['Written', 'Skipped'], [[$result['written'], $result['skipped']]]);
        }

        if (!$this->option('season')) {
            $this->info('Importing ' . $season . ' weekly lines');

            $result = $importer->importStats($season, PlayerStatsResource::WINDOW_WEEK);

            $this->table(['Written', 'Skipped'], [[$result['written'], $result['skipped']]]);
        }

        $this->reportErrors($importer, $result);

        return self::SUCCESS;
    }
}
