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

        $this->import->setUp([
            'year' => text('What year are these rankings for?', date('Y')),
            'ranked_at' => text('What date were these rankings ranked at?', Carbon::now()->toDateString()),
            'ranking_type' => select(
                label: 'Select a ranking type',
                options: $this->rankingTypes,
                default: 'redraft',
            ),
            'ppr' => select(
                label: 'Select a PPR',
                options: [0, 0.5, 1],
                default: 0,
            ),
            'fields_to_import' => $this->fieldsToImport,
        ]);

        $this->import->loadFile();
    }

    public function selectFieldsToImport()
    {
        $fields = array_values(array_intersect(
            array_keys($this->import->driver->dataMap),
            $this->import->getHeaders()
        ));

        $this->fieldsToImport = multiselect(
            label: 'Select fields to import',
            options: $fields,
            required: true,
        );

        $this->import->driver->fieldsToImport = $this->fieldsToImport;
    }

    public function rejectPlayer(array $data): bool
    {
        return Arr::get($data, 'PLAYER NAME') === 'Team';
    }

    public function resolvePlayer(array $data): Player|bool
    {
        $playerName = Arr::get($data, 'PLAYER NAME');

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
        $keys = ['POS', 'Position'];
        $pos = null;

        foreach ($keys as $key) {
            if (Arr::has($data, $key)) {
                $pos = Arr::get($data, $key);
                break;
            }
        }

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
        $keys = ['TEAM', 'Team'];
        $abb = null;

        foreach ($keys as $key) {
            if (Arr::has($data, $key)) {
                $abb = Arr::get($data, $key);
                break;
            }
        }

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
