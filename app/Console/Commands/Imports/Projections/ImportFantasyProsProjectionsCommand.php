<?php

namespace App\Console\Commands\Imports\Projections;

use App\Enums\TeamAbb;
use App\Facades\Action;
use App\Facades\Import;
use App\Models\League;
use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use App\Console\Commands\Traits\DisambiguatesPlayers;
use App\Services\Imports\Importers\PlayerProjectionsImporter;
use App\Enums\RankingSourcesEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
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

    protected ?PlayerProjectionsImporter $import = null;

    protected ?League $league = null;

    protected ?Position $position = null;

    protected array $files = [];

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

        foreach ($this->files as $filePath) {
            $this->loadFile($filePath);
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

        $leagueId = select('Select a league', League::all()->pluck('name', 'id')->toArray());

        $this->league = League::find($leagueId);

        $this->files = multiselect(
            label: 'Select files to import',
            options: $fileOpts,
            scroll: 10,
        );
    }

    public function loadFile(string $filePath)
    {
        $this->info('Importing ' . $filePath . PHP_EOL);

        $this->setConfig($filePath);

        $this->mapFileHeaders();

        $this->import->import();

        $errors = $this->import->getErrors();

        if (! empty($errors)) {
            // $this->table(['Error'], $errors);
            $this->handleErrors($errors);
        }

        $this->info('Import complete' . PHP_EOL);
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

    public function setConfig(string $filePath)
    {
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
            'year' => $year,
            'week' => $week,
            'position' => $pos,
            'ppr' => '0.5',
        ];

        $config['filePath'] = $filePath;
        $config['position'] = $this->getPosition($config['position'])?->id;

        $this->simpleTable($config);

        if (confirm('Is this config correct?')) {
            $this->import->setUp($config);
            return;
        }

        $config = [
            'year' => select(
                'What season?',
                [2025, 2024],
                $year
            ),
            'week' => select(
                'What week?',
                range(1, 18),
                $week
            ),
            'position' => select(
                'What position?',
                Position::all()->pluck('name', 'abbreviation')->toArray(),
                $pos
            ),
            'ppr' => select(
                'Select a PPR',
                ['0', '0.5', '1'],
                '0.5'
            ),
        ];

        $this->import->setUp($config);
    }

    public function mapFileHeaders()
    {
        $defaultDataMap = [
            'player_name' => 'PLAYER NAME',
            'team'        => 'TEAM',
            'points'      => 'PROJ. FPTS',
            'rank'        => 'RK',
        ];

        $dbKeys = array_keys($defaultDataMap);
        $fileKeys = array_values($defaultDataMap);
        $rows = [];

        for ($i = 0; $i < count($dbKeys); $i++) {
            $rows[] = [$dbKeys[$i], $fileKeys[$i]];
        }

        $this->table(['DB Key', 'File Key'], $rows);

        if (confirm('Is this default data mapping correct?')) {
            $this->import->setDataMap($defaultDataMap);
            return;
        }

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

    public function getTeam(string $abb)
    {
        $abb = preg_replace('/[^A-Za-z]/', '', $abb);

        $teamAbb = match($abb) {
            'JAC' => TeamAbb::JAX,
            'WAS' => TeamAbb::WSH,
            default => TeamAbb::from($abb),
        };

        $team = Team::forAbbreviation($teamAbb)->first();

        if (! $team instanceof Team) {
            return false;
        }

        return $team;
    }

    public function handleErrors(array $errors)
    {
        $missingPlayers = array_filter($errors, fn($error) => $error['type'] === 'Player Not Found');

        if (! empty($missingPlayers)) {
            $this->info('There were ' . count($missingPlayers) . ' players not found in the import.');

            if (confirm('Do you want to try to disambiguate these players?')) {
                foreach ($missingPlayers as $player) {
                    $this->mapMissingPlayer($player);
                }
            }
        }
    }

    public function mapMissingPlayer(array $playerData)
    {
        $name = Arr::get($playerData, 'formattedData.player_name');
        $teamAbb = Arr::get($playerData, 'formattedData.team');
        $posId  = $this->import->getConfig('position');

        $team = $this->getTeam($teamAbb);
        $pos = Position::find($posId);

        $player = $this->disambiguatePlayer($name, $pos, $team);

        if (! $player instanceof Player) {
            $this->error('Player not found');
            $this->simpleTable($playerData['fileData'], null, false);
            $this->simpleTable([
                'Player Name' => $name,
                'Team' => $teamAbb . ' [ ' . $team?->id . ' ]',
                'Position' => $pos?->abbreviation . ' [ ' . $pos?->id . ' ]',
            ], null, false);

            if (confirm('Create player?')) {
                $nameSpace = strpos($name, ' ');

                $player = Action::model(Player::class)->create([
                    'first_name' => substr($name, 0, $nameSpace),
                    'last_name' => substr($name, $nameSpace + 1),
                    'full_name' => $name,
                    'position_id' => $pos?->id,
                    'team_id' => $team?->id,
                ]);
            }
        }
    }

    public function simpleTable(array $data, ?array $headers = null, bool $vertical = true)
    {
        $headers = $headers ?? ['Key', 'Value'];
        $rows = [];

        if ($vertical) {
            foreach ($data as $key => $value) {
                $rows[] = [$key, $value];
            }
        } else {
            $headers = array_keys($data);
            $rows = [array_values($data)];
        }

        $this->table($headers, $rows);
    }
}
