<?php

namespace App\Jobs;

use App\Events\LeagueSynced;
use App\Facades\Data;
use App\Models\League;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Re-pull a league from its platform: the league itself, then its rosters.
 *
 * Both calls walk every team and resolve every player, which is far too slow
 * to hold a request open for, so the page queues this and the sync happens
 * behind it.
 *
 * That the sync is running is kept in the cache rather than in the page, so
 * the button reads the same after a refresh as it did before one.
 */
class SyncLeagueJob implements ShouldQueue
{
    use Queueable;

    /**
     * The import talks to the platform for every team, so it is given room to
     * finish.
     */
    public int $timeout = 600;

    /**
     * How long the running flag may outlive the job.
     *
     * A worker killed mid sync never reaches its failed handler, and a button
     * stuck at 'Syncing…' forever is worse than one that gives up: the flag
     * expires on its own a little after the job's own timeout.
     */
    public const RUNNING_TTL = 900;

    public function __construct(public League $league)
    {
        //
    }

    public static function cacheKey(League $league): string
    {
        return 'league-sync-running-' . $league->id;
    }

    public static function isRunning(League $league): bool
    {
        return (bool) Cache::get(self::cacheKey($league), false);
    }

    /**
     * Queue a sync and mark the league as syncing in the same breath, so a
     * page loaded between the dispatch and the job starting still shows it.
     */
    public static function start(League $league): void
    {
        Cache::put(self::cacheKey($league), true, self::RUNNING_TTL);

        self::dispatch($league);
    }

    public function handle(): void
    {
        $league = $this->league;

        try {
            Data::source($league->platform)->importFantasyLeague([
                'created_by_user_id' => $league->created_by_user_id,
                'league_id'          => $league->credentials['leagueId'],
                's2'                 => $league->credentials['s2'],
                'swid'               => $league->credentials['swid'],
                'season'             => $league->season_id,
            ]);

            Data::source($league->platform)->importFantasyRosters($league, $league->season_id);
        } finally {
            Cache::forget(self::cacheKey($league));
        }

        LeagueSynced::dispatch($league);
    }

    /**
     * A failed job has already cleared the flag in handle()'s finally, but the
     * page is still waiting and has to be told.
     */
    public function failed(?Throwable $e): void
    {
        Cache::forget(self::cacheKey($this->league));

        LeagueSynced::dispatch($this->league, 'failed', $e?->getMessage());
    }
}
