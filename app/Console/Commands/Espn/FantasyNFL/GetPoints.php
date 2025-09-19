<?php

namespace App\Console\Commands\Espn\FantasyNFL;

use App\Facades\Espn;
use App\Models\League;
use App\Services\Espn\Resources\FantasyNFL;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use function Laravel\Prompts\select;

class GetPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:ffl:get:points
        { --r|raw : Return raw response  }
        { year?   : Which year to pull   }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL Fantasy League roster from the ESPN API.';

    protected FantasyNFL $api;

    protected League $league;

    protected ?int $year = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $leagueId = select('Select a league', League::all()->pluck('name', 'id')->toArray());

        $this->year = $this->argument('year') ?? select('Select a year', [2025, 2024], 2025);

        $this->league = League::findOrFail($leagueId);

        $this->api = Espn::fantasyNFL($this->league->credentials);

        if ($this->option('raw')) {
            $this->api->returnRaw = true;
        }

        $this->getRosters(1);
    }

    private function getRosters(int $week)
    {
        $this->league->members->each(function ($member) use ($week) {
            $path = $this->getPath($this->league->platform_id, $member->external_id, $this->year, $week);

            $roster = $this->api->getRostersForTeam($member->external_id, $week, $this->year);

            if (! $this->option('raw')) {
                $roster = $this->formatRoster($roster);
            }

            $bytes = file_put_contents($path, json_encode($roster, JSON_PRETTY_PRINT));

            $this->info("NFL Fantasy League Roster saved to $path ($bytes bytes)");
        });

        if ($week <= 17) {
            $this->getRosters($week + 1);
        }
    }

    private function getPath(int|string $leagueId, int|string $memberId, int $year, int $week): string
    {
        $base = 'data/espn/ffl/rosters/';
        $base .= $this->option('raw') ? 'raw' : 'formatted';
        $parts = [$leagueId, 'team', $memberId, $year, 'week', $week];

        return storage_path($base . '/' . implode('-', $parts) . '.json');
    }

    private function formatRoster(array $roster): array
    {
        $data = [];

        foreach (Arr::get($roster, 'teams', []) as $team) {
            foreach (Arr::get($team, 'roster.entries', []) as $entry) {
                $player = Arr::get($entry, 'playerPoolEntry.player');
                $playerId = Arr::get($player, 'id', false);

                if (! $playerId) {
                    dd('Da FUCK?', $player);
                }

                if (! isset($data[$playerId])) {
                    $data[$playerId] = [];
                }

                foreach (Arr::get($player, 'stats', []) as $stat) {
                    $data[$playerId][] = [
                        'game_id'         => Arr::get($stat, 'externalId', false),
                        'points'          => Arr::get($stat, 'appliedTotal', 0),
                        'season'          => Arr::get($stat, 'seasonId', 0),
                        'week'            => Arr::get($stat, 'scoringPeriodId', 0),
                        'is_projected'    => Arr::get($stat, 'statSourceId', 0) === 1,
                        'is_actual'       => Arr::get($stat, 'statSourceId', 0) === 0,
                        'is_season_total' => Arr::get($stat, 'seasonId', 0) === Arr::get($stat, 'scoringPeriodId', -1),
                    ];
                }
            }
        }

        return $data;
    }
}
