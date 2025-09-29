<?php

namespace App\Console\Commands\Data\Imports\Projections;

use App\Console\Commands\Traits\DisambiguatesPlayers;
use App\Enums\DataSources;
use App\Enums\NFLTeams;
use App\Facades\Action;
use App\Facades\FantasyPros;
use App\Facades\Import;
use App\Models\League;
use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use App\Services\FantasyPros\Resources\ProjectionsResource;
use App\Services\Imports\Importers\ProjectionsImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ImportFantasyProsProjectionsCommand extends Command
{
    use DisambiguatesPlayers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:projections:fantasy-pros';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy Pros Projections';

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
            'year' => select('Year', [2025, 2024], 2025),
            'week' => select('Week', range(1, 18), 4),
        ]);

        $this->import->load();

        dd($this->import->getErrors());
    }
}
