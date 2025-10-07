<?php

namespace App\Console\Commands\Data\Imports\Fantasy;

use App\Enums\FantasyPlatforms;
use App\Facades\Data;
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
    protected $signature = 'import:fantasy:league';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy NFL League';

    protected FantasyNFLImporter $importer;

    protected ?League $league = null;

    protected ?FantasyPlatforms $platform = null;

    protected User $creator;

    protected string $action;

    protected array $credentials = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->action = select('Do you want to sync an existing league or create a new one?', ['Create', 'Sync'], 'Create');

        $this->setUp();

        $this->import();

        $this->info($this->league->name . ' imported successfully!');
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

        $this->league = League::findOrFail($leagueId);
        $this->creator = $this->league->creator;
        $this->platform = FantasyPlatforms::from($this->league->platform);
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

        $this->platform = FantasyPlatforms::from(Str::upper($platformArg));
    }

    protected function import()
    {
        $this->info('Importing league...');

        if ($this->platform === FantasyPlatforms::ESPN) {
            return $this->importEspn();
        }
    }

    protected function importEspn()
    {
        $data = [
            'created_by_user_id' => null,
            'league_id' => null,
            's2' => null,
            'swid' => null,
        ];

        if ($this->league instanceof League) {
            $data['created_by_user_id'] = $this->league->creator->id;
            $data['league_id']          = $this->league->credentials['leagueId'];
            $data['s2']                 = $this->league->credentials['s2'];
            $data['swid']               = $this->league->credentials['swid'];

        } else {
            $data['created_by_user_id'] = $this->creator->id;
            $data['league_id']          = intval(text('League ID', config('services.espn.default_league_id')));
            $data['s2']                 = text('S2', config('services.espn.default_s2'));
            $data['swid']               = text('SWID', config('services.espn.default_swid'));
        }

        $this->league = Data::espn()->importFantasyLeague($data);
    }
}
