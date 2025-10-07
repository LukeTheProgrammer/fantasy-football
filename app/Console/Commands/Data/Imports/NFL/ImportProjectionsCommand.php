<?php

namespace App\Console\Commands\Data\Imports\NFL;

use App\Facades\Data;
use App\Models\Season;
use App\Models\Week;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;

class ImportProjectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:nfl:projections
        { year? : Year }
        { week? : Week }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import NFL Projections';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?? select('Year', [2025, 2024], Season::current()->first()->id);
        $week = $this->argument('week') ?? select('Week', range(1, 18), Week::current()->first()->week);

        $this->info('Importing Projections for season ' . $year . ' Week ' . $week);

        $errors = Data::fantasyPros()->importNFLProjections($year, $week);

        if (! empty($errors)) {
            $this->error('Errors found:');
            $this->table(['key', 'val'], array_map(fn ($v, $k) => [$k, json_encode($v)], $errors));
        }

        $this->info('Import complete');
    }
}
