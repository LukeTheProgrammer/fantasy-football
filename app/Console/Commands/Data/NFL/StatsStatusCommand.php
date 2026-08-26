<?php

namespace App\Console\Commands\Data\NFL;

use App\Enums\SeasonType;
use App\Models\NflGame;
use App\Models\PlayerStatWeekly;
use App\Models\PlayerStatYearly;
use App\Models\Season;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Checks an imported season against itself.
 *
 * The strongest check available is that weekly lines and season totals agree,
 * because they are produced separately by the source and only match if both
 * were read correctly. The rest catch the mistakes that survive that: a game
 * that never got linked, a player playing through his bye.
 */
class StatsStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfl:stats:status
        { season? : The season to check, defaults to the current season }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report the coverage and internal agreement of an imported season';

    /**
     * Counting stats that must agree exactly between the two files.
     *
     * @var array<int, string>
     */
    private const TOTALS = [
        'passing_yards', 'passing_touchdowns', 'passing_interceptions',
        'rushing_yards', 'rushing_touchdowns',
        'receiving_receptions', 'receiving_targets', 'receiving_yards', 'receiving_touchdowns',
        'field_goals_made', 'extra_points_made', 'fumbles_lost',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $season = (int) ($this->argument('season') ?? Season::current()->first()?->id);

        $this->info('Checking ' . $season);

        $this->table(['Coverage', 'Rows'], $this->coverage($season));

        $failures = array_merge(
            $this->totalsAgree($season),
            $this->gamesLinked($season),
            $this->byeWeeksEmpty($season),
        );

        if (empty($failures)) {
            $this->info('Every check passed.');

            return self::SUCCESS;
        }

        $this->warn('Checks failed:');
        $this->table(['Check', 'Detail'], $failures);

        return self::FAILURE;
    }

    /**
     * @return array<int, array<int, int|string>>
     */
    private function coverage(int $season): array
    {
        $weekly = PlayerStatWeekly::forSeason($season);

        return [
            ['Weekly lines', (clone $weekly)->count()],
            ['  regular season', (clone $weekly)->regularSeason()->count()],
            ['  postseason', (clone $weekly)->where('season_type', SeasonType::POST)->count()],
            ['  players', (clone $weekly)->distinct()->count('player_id')],
            ['Season totals', PlayerStatYearly::forSeason($season)->count()],
            ['Games', NflGame::forSeason($season)->where('is_bye', false)->count()],
        ];
    }

    /**
     * Weekly lines summed per player must equal the season totals row.
     *
     * @return array<int, array<int, string>>
     */
    private function totalsAgree(int $season): array
    {
        $columns = implode(', ', array_map(
            fn (string $column) => "sum(w.{$column}) as {$column}",
            self::TOTALS
        ));

        $summed = DB::select(
            "select w.player_id, {$columns}, count(*) as games
             from player_stats_weekly w
             where w.season = ? and w.season_type = ? and w.deleted_at is null
             group by w.player_id",
            [$season, SeasonType::REGULAR->value]
        );

        $totals = PlayerStatYearly::forSeason($season)->regularSeason()->get()->keyBy('player_id');

        $failures = [];

        foreach ($summed as $row) {
            $total = $totals->get($row->player_id);

            if ($total === null) {
                $failures[] = ['Totals agree', 'Player ' . $row->player_id . ' has weekly lines but no season total'];

                continue;
            }

            foreach (self::TOTALS as $column) {
                if ((int) $row->{$column} !== (int) $total->{$column}) {
                    $failures[] = ['Totals agree', sprintf(
                        'Player %d %s: weekly %d, season %d',
                        $row->player_id,
                        $column,
                        (int) $row->{$column},
                        (int) $total->{$column}
                    )];
                }
            }
        }

        return $failures;
    }

    /**
     * Every weekly line should know which game it came from.
     *
     * @return array<int, array<int, string>>
     */
    private function gamesLinked(int $season): array
    {
        $unlinked = PlayerStatWeekly::forSeason($season)->whereNull('nfl_game_id')->count();

        return $unlinked === 0
            ? []
            : [['Games linked', $unlinked . ' weekly lines have no game']];
    }

    /**
     * Nobody plays on their bye.
     *
     * @return array<int, array<int, string>>
     */
    private function byeWeeksEmpty(int $season): array
    {
        $failures = [];

        $byes = NflGame::forSeason($season)->where('is_bye', true)->get(['home_team_id', 'week']);

        foreach ($byes as $bye) {
            $played = PlayerStatWeekly::forSeason($season)
                ->forWeek($bye->week)
                ->forTeam($bye->home_team_id)
                ->count();

            if ($played > 0) {
                $failures[] = ['Bye weeks empty', $bye->home_team_id . ' week ' . $bye->week . ': ' . $played . ' lines'];
            }
        }

        return $failures;
    }
}
