<?php

namespace App\Jobs;

use App\Events\DraftPicksSynced;
use App\Events\DraftSyncStopped;
use App\Facades\Auction;
use App\Models\Draft;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * The draft sync loop: pull ESPN, write what is new, tell the room, then queue
 * itself again a few seconds later.
 *
 * A loop rather than the scheduler because the scheduler cannot run more often
 * than once a minute, and a draft moves faster than that.
 */
class SyncDraftPicksJob implements ShouldQueue
{
    use Queueable;

    /**
     * Seconds between polls of ESPN.
     */
    public const INTERVAL = 5;

    /**
     * Consecutive ESPN failures tolerated before the loop gives up.
     */
    public const MAX_FAILURES = 5;

    public function __construct(
        public Draft $draft,
        public string $token,
        public int $failures = 0,
    ) {
        //
    }

    /**
     * The cache key naming the loop that is allowed to be running.
     *
     * Each start writes a fresh token, so pressing start twice does not leave
     * two loops polling ESPN: the older one sees a token that is no longer its
     * own on its next tick and stops.
     */
    public static function tokenKey(Draft $draft): string
    {
        return 'draft-sync-token-' . $draft->id;
    }

    public static function start(Draft $draft): string
    {
        $token = (string) Str::uuid();

        Cache::forever(self::tokenKey($draft), $token);

        self::dispatch($draft, $token);

        return $token;
    }

    public static function stop(Draft $draft): void
    {
        Cache::forget(self::tokenKey($draft));
    }

    public function handle(): void
    {
        $draft = $this->draft->fresh();

        if (!$draft instanceof Draft || !$draft->is_active) {
            return;
        }

        if (Cache::get(self::tokenKey($draft)) !== $this->token) {
            return;
        }

        try {
            $result = Auction::syncEspnPicks($draft);
        } catch (Throwable $e) {
            $this->handleFailure($draft, $e);

            return;
        }

        $created = count(Arr::get($result, 'created', []));
        $updated = (int) Arr::get($result, 'updated', 0);
        $skipped = (int) Arr::get($result, 'skipped', 0);
        $completed = (bool) Arr::get($result, 'is_completed');

        // A quiet poll is the normal case, so the room is only disturbed when
        // the board actually changed.
        if ($created > 0 || $updated > 0 || $skipped > 0) {
            DraftPicksSynced::dispatch($draft, $created, $updated, $skipped, $completed);
        }

        if ($completed) {
            $draft->update(['is_completed' => true, 'is_active' => false]);

            self::stop($draft);

            DraftSyncStopped::dispatch($draft, 'completed');

            return;
        }

        self::dispatch($draft, $this->token)->delay(now()->addSeconds(self::INTERVAL));
    }

    /**
     * An expired cookie or a blip at ESPN must not end the sync in the middle
     * of a draft, so failures are counted and only a run of them stops it.
     */
    private function handleFailure(Draft $draft, Throwable $e): void
    {
        Log::error('Draft sync failed', [
            'draft_id' => $draft->id,
            'failures' => $this->failures + 1,
            'message'  => $e->getMessage(),
        ]);

        if ($this->failures + 1 >= self::MAX_FAILURES) {
            $draft->update(['is_active' => false]);

            self::stop($draft);

            DraftSyncStopped::dispatch($draft, 'failed', $e->getMessage());

            return;
        }

        self::dispatch($draft, $this->token, $this->failures + 1)
            ->delay(now()->addSeconds(self::INTERVAL));
    }
}
