<?php

namespace App\Console\Commands\Data\Imports\NFL;

use App\Enums\DataSources;
use App\Facades\Import;
use App\Models\Season;
use App\Models\Week;
use App\Services\Imports\Importers\ProjectionsImporter;
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
        {year? : Year}
        {week? : Week}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import NFL Projections';

    protected ?ProjectionsImporter $import = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->import = Import::projections(
            DataSources::FANTASY_PROS->value
        );

        $this->import->setUp([
            'year' => $this->argument('year') ?? select('Year', [2025, 2024], Season::current()->first()->id),
            'week' => $this->argument('week') ?? select('Week', range(1, 18), Week::current()->first()->week),
        ]);

        $this->import->load();

        $errors = $this->import->getErrors();

        if (! empty($errors)) {
            $this->error('Errors found:');
            $this->table(['key', 'val'], array_map(fn ($v, $k) => [$k, json_encode($v)], $errors));
        }

        $this->info('Import complete');
    }
}
