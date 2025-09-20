<?php

namespace App\Console\Commands\Imports\FantasyNFL;

use App\Enums\FantasyPlatformsEnum;
use App\Facades\Espn;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueMemberRoster;
use App\Models\Player;
use App\Services\Espn\Resources\FantasyNFL;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

class ImportFantasyPointsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fantasy-nfl:points
        { leagueId? : League to import }
        { year?     : Year to import   }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Fantasy NFL Points';

    protected FantasyNFL $api;

    protected League $league;

    protected ?int $year = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setUp();

        $this->info('Importing Fantasy Points');

        $this->import();

        $this->info('Fantasy Points imported successfully: ' . $this->league->name);
    }

    protected function setUp()
    {
        $leagueId = $this->argument('leagueId') ?? select(
            label: 'League',
            options: League::all()->pluck('name', 'id')->toArray(),
            default: null,
        );

        $this->year = $this->argument('year') ?? select('Select a year', [2025, 2024], 2025);

        $this->league = League::findOrFail($leagueId);

        $platform = FantasyPlatformsEnum::from(Str::upper($this->league->platform));

        if ($platform === FantasyPlatformsEnum::ESPN) {
            return $this->setUpEspnImporter();
        }
    }

    protected function setUpEspnImporter()
    {
        $this->api = Espn::fantasyNFL($this->league->credentials);
    }

    protected function import()
    {
        $this->league->members->each(
            fn (LeagueMember $member) => $this->processLeagueMember($member)
        );
    }

    protected function processLeagueMember(LeagueMember $member)
    {
        $fp = $this->datafilePath($member);
        $fd = file_get_contents($fp);
        $data = json_decode($fd, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            dd('JSON Error: ' . json_last_error_msg(), $fp);
        }

        foreach ($data as $week => $roster) {
            foreach ($roster as $player) {
                $playerModel = Player::espnId(Arr::get($player, 'player_id', null))->first();

                if (! $playerModel instanceof Player) {
                    dd('Player not found: ' . json_encode($player));
                }

                LeagueMemberRoster::updateOrCreate(
                    [
                        'league_member_id'   => $member->id,
                        'nfl_game_id' => Arr::get($player, 'nfl_game_id', null),
                        'player_id'   => $playerModel->id,
                    ],
                    Arr::only($player, [
                        'season',
                        'week',
                        'lineup_slot_id',
                        'position_rank',
                        'overall_rank',
                        'fantasy_points',
                        'espn_projected_points',
                        'percent_owned',
                        'percent_started',
                        'percent_changed',
                    ]),
                );
            }
        }
    }

    private function datafilePath(LeagueMember $member): string
    {
        // storage/data/espn/ffl/rosters/formatted/691509-team-4-year-2024.json
        $fn = implode('-', [
            $member->league->platform_id,
            'team',
            $member->external_id,
            'year',
            $this->year,
        ]);

        return storage_path('data/espn/ffl/rosters/formatted/' . $fn . '.json');
    }
}
