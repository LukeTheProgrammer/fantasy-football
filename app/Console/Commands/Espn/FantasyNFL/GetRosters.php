<?php

namespace App\Console\Commands\Espn\FantasyNFL;

use App\Facades\Espn;
use App\Models\League;
use App\Models\NflGame;
use App\Models\LeagueMember;
use App\Services\Espn\Resources\FantasyNFL;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use function Laravel\Prompts\select;

class GetRosters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:ffl:get:rosters
        { leagueId? : League to pull }
        { year?     : Year to pull   }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads NFL Fantasy League rosters from the ESPN API.';

    protected FantasyNFL $api;

    protected League $league;

    protected array $nflGameIds = [];

    protected ?int $year = null;

    protected ?int $week = null;

    protected string $rawPath = '';

    protected string $formattedPath = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->nflGameIds = NflGame::select(['id', 'espn_id'])
            ->get()
            ->mapWithKeys(fn ($g) => [$g->espn_id => $g->id])
            ->toArray();

        $leagueId = $this->argument('leagueId') ?? select('Select a league', League::all()->pluck('name', 'id')->toArray());

        $this->year = $this->argument('year') ?? select('Select a year', [2025, 2024], 2025);

        $this->league = League::findOrFail($leagueId);

        $this->api = Espn::fantasyNFL($this->league->credentials);

        $this->api->returnRaw = true;

        $this->getRosters(1);
    }

    private function getRosters(int $week)
    {
        $this->week = $week;

        $this->info('Getting Rosters for Season ' . $this->year . ' Week ' . $this->week);

        $bar = $this->output->createProgressBar($this->league->members->count());
        $bar->start();

        $this->league->members->each(function ($member) use ($bar) {
            $bar->advance();
            $this->rawPath = $this->getPath(true, $member->external_id, $this->year);
            $this->formattedPath = $this->getPath(false, $member->external_id, $this->year);

            $roster = $this->api->getRostersForTeam($member->external_id, $this->week, $this->year);

            $this->saveRoster($roster, true);

            $this->saveFormattedRoster($this->formatRoster($roster, $member));
        });

        $bar->finish();
        echo PHP_EOL . PHP_EOL;

        if ($week <= 17) {
            $this->getRosters($week + 1);
        }
    }

    private function getPath(bool $raw, int|string $memberId, int $year): string
    {
        $base = 'data/espn/ffl/rosters/';
        $base .= $raw ? 'raw' : 'formatted';
        $parts = [$this->league->platform_id, 'team', $memberId, 'year', $year];

        if ($raw) {
            $parts[] = 'week';
            $parts[] = $this->week;
        }

        return storage_path($base . '/' . implode('-', $parts) . '.json');
    }

    private function formatRoster(array $roster, LeagueMember $member): array
    {
        $data = [];

        foreach (Arr::get($roster, 'teams', []) as $team) {
            foreach (Arr::get($team, 'roster.entries', []) as $entry) {
                $player = Arr::get($entry, 'playerPoolEntry.player', []);
                $ratings = Arr::get($entry, 'playerPoolEntry.ratings', []);
                $playerId = Arr::get($player, 'id', false);
                $stats = Arr::get($player, 'stats', []);

                if (! $playerId) {
                    dd('Da FUCK?', $player);
                }

                foreach ($stats as $stat) {
                    $statWeek = Arr::get($stat, 'scoringPeriodId', -1);
                    if ($statWeek != $this->week) {
                        continue;
                    }

                    if (! isset($data[$playerId])) {
                        $data[$playerId] = [
                            'league_member_id'      => $member->id,
                            'player_id'             => $playerId,
                            'season'                => $this->year,
                            'week'                  => $this->week,
                            'nfl_game_id'           => null,
                            'fantasy_points'        => 0,
                            'espn_projected_points' => 0,
                            'lineup_slot_id'        => Arr::get($entry, 'lineupSlotId', false),
                            'position_rank'         => Arr::get($ratings, '0.positionalRanking', 999999),
                            'overall_rank'          => Arr::get($ratings, '0.totalRanking', 999999),
                            'percent_owned'         => Arr::get($player, 'ownership.percentOwned', 0),
                            'percent_started'       => Arr::get($player, 'ownership.percentStarted', 0),
                            'percent_changed'       => Arr::get($player, 'ownership.percentChange', 0),
                        ];
                    }

                    $data[$playerId] = $this->processStat($stat, $data[$playerId]);
                }
            }
        }

        return $data;
    }

    private function processStat(array $stat, array $data = [])
    {
        $gameId  = intval(Arr::get($stat, 'externalId', false));
        $year    = intval(Arr::get($stat, 'seasonId', false));
        $week    = intval(Arr::get($stat, 'scoringPeriodId', false));
        $points  = floatVal(Arr::get($stat, 'appliedTotal', 0));

        $isProjection = Arr::get($stat, 'statSourceId', false) === 1;

        if ($gameId == $year . $week && $isProjection) {
            $data['espn_projected_points'] = $points;
        }

        if (isset($this->nflGameIds[$gameId])) {
            $data['nfl_game_id'] = $this->nflGameIds[$gameId];
            $data['points'] = $points;
        }

        return $data;
    }

    private function saveFormattedRoster(array $roster): void
    {
        $d = [];

        if (file_exists($this->formattedPath)) {
            $j = file_get_contents($this->formattedPath);
            $d = json_decode($j, true);
        }

        $d['week ' . $this->week] = $roster;

        $this->saveRoster($d, false);
    }

    private function saveRoster(array $roster, bool $raw): void
    {
        $path = $raw ? $this->rawPath : $this->formattedPath;

        file_put_contents($path, json_encode($roster, JSON_PRETTY_PRINT));
    }
}
