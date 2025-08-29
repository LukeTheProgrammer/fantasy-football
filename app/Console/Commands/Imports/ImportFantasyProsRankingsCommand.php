<?php

namespace App\Console\Commands\Imports;

use App\Facades\Import;
use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use App\Console\Commands\Traits\DisambiguatesPlayers;
use App\Services\Imports\Models\DraftRankingsImport;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;

class ImportFantasyProsRankingsCommand extends Command
{
    use DisambiguatesPlayers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fantasy-pros:rankings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy Pros Rankings';

    protected ?DraftRankingsImport $import = null;

    protected array $fieldsToImport = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fileDir = base_path('database/data/rankings/FantasyPros');

        $files = glob($fileDir . '/*.csv');

        $file = select('Select a file to import', $files);
        // dd($file);

        $this->import = Import::draftRankingsImport('fantasy-pros', $file, 'csv');
        // dd($import);

        $this->import->setUp();

        $this->import->loadFile();

        while (($line = $this->import->getNextLine()) !== false) {
            if (is_array($line)) {
                if (empty($this->fieldsToImport)) {
                    $this->selectFieldsToImport();
                }

                $player = $this->resolvePlayer($line);

                if ($player instanceof Player) {
                    $this->info('Importing ' . $player->full_name . ' ranking');
                    $this->import->saveRanking(
                        $player,
                        $line,
                        $this->fieldsToImport,
                    );
                }
            }
        }

        $this->call('draft:calculate-rankings');
    }

    public function selectFieldsToImport()
    {
        $this->fieldsToImport = multiselect(
            label: 'Select fields to import',
            options: $this->import->getHeaders(),
            required: true,
        );
    }

    public function resolvePlayer(array $data): Player|bool
    {
        $playerName = Arr::get($data, 'PLAYER NAME');

        $positionAbb = preg_replace('/\d/', '', Arr::get($data, 'POS'));
        $position = ($positionAbb !== null) ? Position::forAbbreviation($positionAbb)->first() : null;

        $teamAbb = Arr::get($data, 'TEAM');
        $team = ($teamAbb !== null) ? Team::forAbbreviation($teamAbb)->first() : null;

        $player = $this->disambiguatePlayer($playerName, $position, $team);

        return $player;
    }
}
