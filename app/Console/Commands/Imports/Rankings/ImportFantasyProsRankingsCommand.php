<?php

namespace App\Console\Commands\Imports\Rankings;

use App\Facades\Action;
use App\Facades\Import;
use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use App\Console\Commands\Traits\DisambiguatesPlayers;
use App\Services\Imports\Models\DraftRankingsImport;
use App\Enums\RankingSourcesEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ImportFantasyProsRankingsCommand extends Command
{
    use DisambiguatesPlayers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:rankings:fantasy-pros';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy Pros Rankings';

    protected ?DraftRankingsImport $import = null;

    protected array $fileHeaders = [];

    protected array $dataMap = [];

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

        while (($line = $this->import->getNextLine()) !== false) {
            if (is_array($line)) {
                // if (empty($this->fieldsToImport)) {
                //     $this->selectFieldsToImport();
                // }

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

        if (! empty($this->errors)) {
            $this->table(['Error', 'Data'], $this->errors);
        }

        $this->info('Import complete');

        // $this->call('draft:calculate-rankings');
    }

    public function setUp()
    {
        $fileDir = base_path('database/data/rankings/FantasyPros');

        $files = glob($fileDir . '/*.csv');

        $file = select('Select a file to import', $files);

        $this->import = Import::draftRankingsImport(
            RankingSourcesEnum::FANTASY_PROS->value,
            $file,
            'csv',
        );

        // Just for testing
        $this->import->setUp([
            'year' => date('Y'),
            'ranked_at' => Carbon::now()->toDateString(),
            'ranking_type' => 'redraft',
            'ppr' => 0.5,
        ]);

        // $this->import->setUp([
        //     'year' => text('What year are these rankings for?', date('Y')),
        //     'ranked_at' => text('What date were these rankings ranked at?', Carbon::now()->toDateString()),
        //     'ranking_type' => select(
        //         label: 'Select a ranking type',
        //         options: $this->rankingTypes,
        //         default: 'redraft',
        //     ),
        //     'ppr' => select(
        //         label: 'Select a PPR',
        //         options: [0, 0.5, 1],
        //         default: 0,
        //     ),
        //     'fields_to_import' => $this->fieldsToImport,
        // ]);

        $this->import->loadFile();

        $this->fileHeaders = $this->import->getHeaders();

        $this->mapFileHeaders();
    }

    public function mapFileHeaders()
    {
        $props = [
            'player_name',
            'team',
            'position',
            'rank',
            'tier',
            'adp',
            'adv',
        ];

        // Make the keys and values the same
        $options = array_combine($this->fileHeaders, $this->fileHeaders);

        $options['_NULL_'] = 'None';

        $kvPairs = [];

        foreach ($props as $dbProp) {
            $fileKey = select(
                label: 'Which column is ' . $dbProp,
                options: $options,
                default: '_NULL_',
            );

            $this->dataMap[$dbProp] = $fileKey;
            $kvPairs[] = [$dbProp, $fileKey];
        }

        $this->dataMap = array_filter($this->dataMap, fn($v) => $v !== '_NULL_');

        $this->import->driver->dataMap = $this->dataMap;

        $this->table(['Key', 'Value'], $kvPairs);

        if (! confirm('Is this correct?')) {
            // Allow User to retry
            return $this->mapFileHeaders();
        }
    }

    public function resolvePlayer(array $data): Player|bool
    {
        $nameKey = $this->dataMap['player_name'];
        $playerName = Arr::get($data, $nameKey);

        $position = $this->getPosition($data);

        if (! $position instanceof Position) {
            $this->errors[] = ['Position not found', json_encode($data)];
            return false;
        }

        $team = $this->getTeam($data);

        if (! $team instanceof Team) {
            $this->errors[] = ['Team not found', json_encode($data)];
            return false;
        }

        $player = $this->disambiguatePlayer($playerName, $position, $team);

        if (! $player instanceof Player) {
            $this->errors[] = ['Player not found', json_encode($data)];
            return false;
        }

        return $player;
    }

    public function getPosition(array $data)
    {
        $key = $this->dataMap['position'];
        $pos = Arr::get($data, $key);

        if (empty($pos)) {
            return false;
        }

        $pos = preg_replace('/\d/', '', $pos);

        $position = Position::forAbbreviation($pos)->first();

        if (! $position instanceof Position) {
            return false;
        }

        return $position;
    }

    public function getTeam(array $data)
    {
        $key = $this->dataMap['team'];
        $abb = Arr::get($data, $key);

        if (empty($abb)) {
            return false;
        }

        $tamAbb = match ($abb) {
            'ARI' => 'ARZ',
            'JAC' => 'JAX',
            default => $abb,
        };

        $team = Team::forAbbreviation($tamAbb)->first();

        if (! $team instanceof Team) {
            return false;
        }

        return $team;
    }
}
