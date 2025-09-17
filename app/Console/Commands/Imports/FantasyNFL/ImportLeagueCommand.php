<?php

namespace App\Console\Commands\Imports\FantasyNFL;

use App\Enums\FantasyPlatformsEnum;
use App\Facades\Import;
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
    protected $signature = 'import:fantasy-nfl:league
        { platform? : Platform where league is played }
    ';

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

    public function setUp()
    {
        $creatorId = select('Creator', User::all()->pluck('name', 'id')->toArray());

        $this->creator = User::findOrFail($creatorId);

        $platformArg = $this->argument('platform') ?? select(
            label: 'Platform',
            options: FantasyPlatformsEnum::options()->toArray(),
            default: FantasyPlatformsEnum::ESPN->value
        );

        $platform = FantasyPlatformsEnum::from(Str::upper($platformArg));

        if ($platform === FantasyPlatformsEnum::ESPN) {
            return $this->setUpEspnImporter();
        }
    }

    public function setUpEspnImporter()
    {
        $this->importer = Import::fantasyNFL(FantasyPlatformsEnum::ESPN);

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
