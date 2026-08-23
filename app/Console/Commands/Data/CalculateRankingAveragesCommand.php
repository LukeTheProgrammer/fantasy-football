<?php

namespace App\Console\Commands\Data;

use App\Models\PlayerRankingAverage;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CalculateRankingAveragesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rankings:calculate-averages
        { --date=   : Date to snapshot, defaults to today }
        { --season= : Limit to a single season }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Snapshot the cross-source average of each player\'s draft rankings for a single day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->toDateString()
            : Carbon::today()->toDateString();

        $season = $this->option('season');

        $averages = $this->averages($date, $season);

        if ($averages->isEmpty()) {
            $this->warn('No rankings found on or before ' . $date . '.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($averages->count());

        $averages->chunk(500)->each(function ($chunk) use ($date, $bar) {
            PlayerRankingAverage::upsert(
                $chunk->map(fn ($row) => [
                    'player_id' => $row->player_id,
                    'season'    => $row->season,
                    'ranked_on' => $date,
                    'type'      => $row->type,
                    'ppr'       => $row->ppr,
                    'rank'      => $row->rank,
                    'tier'      => $row->tier,
                    'adp'       => $row->adp,
                    'adv'       => $row->adv,
                ])->all(),
                ['player_id', 'season', 'ranked_on', 'type', 'ppr'],
                ['rank', 'tier', 'adp', 'adv']
            );

            $bar->advance($chunk->count());
        });

        $bar->finish();
        $this->newLine();
        $this->info($averages->count() . ' ranking averages snapshotted for ' . $date . '.');

        return self::SUCCESS;
    }

    /**
     * Average each measure across sources, one row per player, season, type and
     * scoring format.
     *
     * Zeroes are excluded from the averages so that a source which does not
     * publish a given measure cannot drag the average down.
     *
     * @param string $date
     * @param int|string|null $season
     *
     * @return Collection
     */
    private function averages(string $date, int|string|null $season = null)
    {
        return DB::table('draft_rankings as rankings')
            ->joinSub(
                $this->latestPerSource($date, $season),
                'latest',
                fn ($join) => $join->on('rankings.player_id', '=', 'latest.player_id')
                    ->on('rankings.season', '=', 'latest.season')
                    ->on('rankings.type', '=', 'latest.type')
                    ->on('rankings.ppr', '=', 'latest.ppr')
                    ->on('rankings.ranked_at', '=', 'latest.ranked_at')
                    // Null safe: source is nullable, and NULL = NULL is never true.
                    ->whereRaw('rankings.source <=> latest.source')
            )
            ->whereNull('rankings.deleted_at')
            ->groupBy('rankings.player_id', 'rankings.season', 'rankings.type', 'rankings.ppr')
            ->select([
                'rankings.player_id',
                'rankings.season',
                'rankings.type',
                'rankings.ppr',
            ])
            ->selectRaw('AVG(NULLIF(rankings.`rank`, 0)) as `rank`')
            ->selectRaw('AVG(NULLIF(rankings.tier, 0)) as tier')
            ->selectRaw('AVG(NULLIF(rankings.adp, 0)) as adp')
            ->selectRaw('AVG(NULLIF(rankings.adv, 0)) as adv')
            ->get();
    }

    /**
     * The most recent ranking each source published on or before the snapshot
     * date, so that a source which skips a day still counts toward that day.
     *
     * @param string $date
     * @param int|string|null $season
     *
     * @return Builder
     */
    private function latestPerSource(string $date, int|string|null $season = null)
    {
        return DB::table('draft_rankings')
            ->whereNull('deleted_at')
            ->whereDate('ranked_at', '<=', $date)
            ->when($season, fn ($query) => $query->where('season', $season))
            ->groupBy('player_id', 'season', 'type', 'ppr', 'source')
            ->select(['player_id', 'season', 'type', 'ppr', 'source'])
            ->selectRaw('MAX(ranked_at) as ranked_at');
    }
}
