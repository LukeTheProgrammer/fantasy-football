<?php

namespace App\Console\Commands\Data\Imports;

use App\Enums\FantasyPlatforms;
use App\Facades\Import;
use App\Models\League;
use App\Models\User;
use App\Services\Imports\Importers\FantasyNFLImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class FantasyLeagueImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fantasy:league';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy NFL League';

    protected string $action;

    protected FantasyNFLImporter $importer;

    protected array $credentials = [];

    protected User $creator;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->action = select('Do you want to sync an existing league or create a new one?', ['Create', 'Sync'], 'Create');

        $this->setUp();

        $this->info('Importing league...');

        $league = $this->importer->importLeague($this->creator, $this->credentials);

        $this->info($league->name . ' imported successfully!');
    }

    protected function setUp()
    {
        if ($this->action === 'Sync') {
            return $this->setUpSync();
        }

        return $this->setUpCreate();
    }

    protected function setUpSync()
    {
        $leagueId = select('League', League::all()->pluck('name', 'id')->toArray());

        $league = League::findOrFail($leagueId);

        $this->creator = $league->creator;

        if ($league->platform === FantasyPlatforms::ESPN->value) {
            return $this->setUpEspnImporter($league);
        }
    }

    protected function setUpCreate()
    {
        $creatorId = select('Creator', User::all()->pluck('name', 'id')->toArray());

        $this->creator = User::findOrFail($creatorId);

        $platformArg = select(
            label: 'Platform',
            options: FantasyPlatforms::options()->toArray(),
            default: FantasyPlatforms::ESPN->value
        );

        $platform = FantasyPlatforms::from(Str::upper($platformArg));

        if ($platform === FantasyPlatforms::ESPN) {
            return $this->setUpEspnImporter();
        }
    }

    protected function setUpEspnImporter(?League $league = null)
    {
        $this->importer = Import::fantasyNFL(FantasyPlatforms::ESPN);

        if ($league instanceof League) {
            $this->credentials = $league->credentials;
            $this->creator = $league->creator;
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
        }
    }
}
