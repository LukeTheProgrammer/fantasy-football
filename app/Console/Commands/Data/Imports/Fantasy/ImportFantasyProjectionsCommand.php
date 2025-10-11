<?php

namespace App\Console\Commands\Data\Imports\Fantasy;

use App\Facades\Data;
use App\Models\Season;
use App\Models\Week;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;

class ImportFantasyProjectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fantasy:projections
        { season? : Season }
        { week? : Week }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy Projections';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $season = $this->argument('season') ?? select('Season', [2025, 2024], Season::current()->first()->id);
        $week = $this->argument('week');

        if ($week) {
            $this->importWeek($season, $week);
        } else {
            Week::forSeason($season)->get()->each(function (Week $week) use ($season) {
                $this->importWeek($season, $week->week);

                if ($week->is_current) {
                    return false;
                }
            });
        }

        $this->info('Import complete');
    }

    private function importWeek(int $season, int $week)
    {
        $this->info('Importing Projections for season ' . $season . ' Week ' . $week);

        $errors = Data::fantasyPros()->importNFLProjections($season, $week);

        if (! empty($errors)) {
            $this->displayErrors($errors);
        }
    }

    private function displayErrors(array $errors): void
    {
        $this->error('Errors found:');

        $rows = [];

        foreach ($errors as $k => $v) {
            $rows[] = [$k, json_encode($v)];
        }

        $this->table(['key', 'val'], $rows);
    }
}
