<?php

namespace App\Console\Commands\Imports\FantasyNFL;

use App\Enums\FantasyPlatformsEnum;
use App\Facades\Import;
use App\Models\League;
use App\Models\User;
use App\Services\Imports\Importers\FantasyNFLImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ImportLeagueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fantasy-nfl:league';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy NFL League';

    protected FantasyNFLImporter $importer;

    protected array $credentials = [];

    protected User $creator;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setUp();

        $this->info('Importing league...');

        $league = $this->importer->import();

        $this->info('League imported successfully: ' . $league->name);
    }

    protected function setUp()
    {
        $action = select('Do you want to sync an existing league or create a new one?', ['Sync', 'Create']);

        if ($action === 'Sync') {
            return $this->setUpSync();
        }

        return $this->setUpCreate();
    }

    protected function setUpSync()
    {
        $leagueId = select('League', League::all()->pluck('name', 'id')->toArray());

        $league = League::findOrFail($leagueId);

        $this->creator = $league->creator;

        if ($league->platform === FantasyPlatformsEnum::ESPN->value) {
            return $this->setUpEspnImporter($league);
        }
    }

    protected function setUpCreate()
    {
        $creatorId = select('Creator', User::all()->pluck('name', 'id')->toArray());

        $this->creator = User::findOrFail($creatorId);

        $platformArg = select(
            label: 'Platform',
            options: FantasyPlatformsEnum::options()->toArray(),
            default: FantasyPlatformsEnum::ESPN->value
        );

        $platform = FantasyPlatformsEnum::from(Str::upper($platformArg));

        if ($platform === FantasyPlatformsEnum::ESPN) {
            return $this->setUpEspnImporter();
        }
    }

    protected function setUpEspnImporter(?League $league = null)
    {
        $this->importer = Import::fantasyNFL(FantasyPlatformsEnum::ESPN);

        if ($league instanceof League) {
            $this->importer->setCredentials($league->credentials);
            $this->importer->setCreator($league->creator);
        } else {
            $this->credentials = [
                'leagueId' => intval(text(
                    label: 'League ID',
                    default: config('services.espn.default_league_id'),
                )),
                's2' => text(
                    label: 'S2',
                    default: config('services.espn.default_s2'),
                ),
                'swid' => text(
                    label: 'SWID',
                    default: config('services.espn.default_swid'),
                ),
            ];

            $this->importer->setCredentials($this->credentials);

            $this->importer->setCreator($this->creator);
        }
    }
}
