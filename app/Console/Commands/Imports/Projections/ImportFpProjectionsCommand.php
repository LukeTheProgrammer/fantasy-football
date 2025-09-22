<?php

namespace App\Console\Commands\Imports\Projections;

use App\Facades\Import;
use App\Models\League;
use App\Models\Position;
use App\Console\Commands\Traits\DisambiguatesPlayers;
use App\Services\Imports\Importers\PlayerProjectionsImporter;
use App\Enums\RankingSourcesEnum;
use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ImportFpProjectionsCommand extends Command
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

    protected ?PlayerProjectionsImporter $import = null;

    protected ?League $league = null;

    protected ?Position $position = null;

    protected array $fieldsToImport = [];

    protected array $rankingTypes = [
        'redraft',
        'dynasty',
    ];

    protected array $errors = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setUp();

        $this->import->import();

        $errors = $this->import->getErrors();

        if (! empty($errors)) {
            // $this->table(['Error'], $errors);
            dd($errors);
        }

        $this->info('Import complete');

        // $this->call('draft:calculate-rankings');
    }

    public function setUp()
    {
        $fileOpts = $this->getFileOpts(
            storage_path('data/rankings/fantasy-pros')
        );

        if (empty($fileOpts)) {
            $this->error('No files found');
            return;
        }

        $filePath = select('Select a file to import', $fileOpts);

        $leagueId = select('Select a league', League::all()->pluck('name', 'id')->toArray());

        $this->league = League::find($leagueId);

        $this->import = Import::playerProjections(
            RankingSourcesEnum::FANTASY_PROS->value
        );

        // Ex file name: FantasyPros_2025_Week_3_QB_Rankings.csv
        $fileName = basename($filePath);

        $fn = str_replace('FantasyPros_', '', $fileName);
        $fn = str_replace('_Rankings.csv', '', $fn);
        $fn = str_replace('Week_', '', $fn);
        $fnData = explode('_', $fn);

        $year = preg_replace('/[^0-9]/', '', $fnData[0]);
        $week = preg_replace('/[^0-9]/', '', $fnData[1]);
        $pos  = preg_replace('/[^A-Za-z]/', '', $fnData[2]);

        $config = [
            // 'year' => select(
            //     'What season?',
            //     [2025, 2024],
            //     $year
            // ),
            // 'week' => select(
            //     'What week?',
            //     range(1, 18),
            //     $week
            // ),
            // 'position' => select(
            //     'What position?',
            //     Position::all()->pluck('name', 'abbreviation')->toArray(),
            //     $pos
            // ),
            // 'ppr' => select(
            //     'Select a PPR',
            //     ['0', '0.5', '1'],
            //     '0.5'
            // ),
            'year' => $year,
            'week' => $week,
            'position' => $pos,
            'ppr' => '0.5',
        ];

        $config['filePath'] = $filePath;
        $config['position'] = $this->getPosition($config['position'])?->id;

        $this->import->setUp($config);

        $this->mapFileHeaders();
    }

    public function getFileOpts(string $dir, array $fileOpts = []): array
    {
        $exclude = [
            '.',
            '..',
            '.DS_Store',
            '.gitignore',
            '.gitkeep',
        ];

        $files = array_filter(scandir($dir), fn($dir) => ! in_array($dir, $exclude));

        foreach ($files as $file) {
            if (is_dir($dir . '/' . $file)) {
                $fileOpts = $this->getFileOpts($dir . '/' . $file, $fileOpts);
            }

            if (is_file($dir . '/' . $file)) {
                $fileOpts[] = str_replace(storage_path(), '', $dir . '/' . $file);
            }
        }

        return $fileOpts;
    }

    public function mapFileHeaders()
    {
        $dataProps = $this->import->dataProps();
        $fileProps = $this->import->fileProps();
        $dataMap = [];

        // Keys and vals the same for select opts
        $options = array_merge(
            ['_NULL_' => 'None'],
            array_combine($fileProps, $fileProps)
        );

        $kvPairs = [];

        foreach ($dataProps as $dbProp) {
            $fileKey = select(
                label: 'Which column is ' . $dbProp,
                options: $options,
                default: '_NULL_',
            );

            $dataMap[$dbProp] = $fileKey;
            $kvPairs[] = [$dbProp, $fileKey];
        }

        $dataMap = array_filter($dataMap, fn($v) => $v !== '_NULL_');

        $this->import->setDataMap($dataMap);

        $this->table(['DB Key', 'File Key'], $kvPairs);

        if (! confirm('Is this correct?')) {
            // Allow User to retry
            return $this->mapFileHeaders();
        }
    }

    public function getPosition(string $pos)
    {
        $pos = preg_replace('/\d/', '', $pos);

        $position = Position::forAbbreviation($pos)->first();

        if (! $position instanceof Position) {
            return false;
        }

        return $position;
    }
}
