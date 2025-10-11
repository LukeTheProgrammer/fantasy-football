<?php

namespace App\Console\Commands\Data\Imports\Fantasy;

use App\Enums\FantasyPlatforms;
use App\Facades\Data;
use App\Models\League;
use App\Models\User;
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
    protected $signature = 'import:fantasy:league
        { --create       : Create a new league }
        { --sync         : Sync an existing league }
        { --platform=    : Platform to pull }
        { --platform-id= : Platform ID to pull }
        { --creator-id=  : Creator ID to pull }
        { --league-id=   : League ID to sync }
        { --season=      : Season to pull }
        { --espn-s2=     : ESPN S2 to pull }
        { --espn-swid=   : ESPN SWID to pull }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy NFL League';

    protected ?League $league = null;

    protected ?FantasyPlatforms $platform = null;

    protected ?User $creator = null;

    protected string $action;

    protected array $credentials = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setUp();

        $this->import();

        $this->info($this->league->name . ' imported successfully!');

        $this->league = null;
        $this->platform = null;
        $this->creator = null;
        $this->action = '';
        $this->credentials = [];
    }

    protected function setUp()
    {
        if ($this->option('create')) {
            $this->action = 'Create';

        } elseif ($this->option('sync')) {
            $this->action = 'Sync';

        } else {
            $this->action = select('Do you want to sync an existing league or create a new one?', ['Create', 'Sync'], 'Create');
        }

        if ($this->action === 'Sync') {
            return $this->setUpSync();
        }

        return $this->setUpCreate();
    }

    protected function setUpSync()
    {
        $leagueId = $this->option('league-id') ?? select('League', League::all()->pluck('name', 'id')->toArray());

        $this->league = League::findOrFail($leagueId);
        $this->creator = $this->league->creator;
        $this->platform = FantasyPlatforms::from($this->league->platform);
    }

    protected function setUpCreate()
    {
        $creatorId = $this->option('creator-id') ?? select('Creator', User::all()->pluck('name', 'id')->toArray());

        $this->creator = User::findOrFail($creatorId);

        $platformArg = $this->option('platform') ?? select(
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
            $data['league_id']          = intval($this->option('platform-id') ?? text('League ID', 'League ID', config('services.espn.default_league_id')));
            $data['s2']                 = $this->option('espn-s2') ?? text('S2', 'S2', config('services.espn.default_s2'));
            $data['swid']               = $this->option('espn-swid') ?? text('SWID', 'SWID', config('services.espn.default_swid'));
        }

        $this->info('Importing ESPN League ' . $data['league_id'] . ' for user ' . $data['created_by_user_id']);

        $this->league = Data::espn()->importFantasyLeague($data);
    }
}
