<?php

namespace App\Console\Commands\Drafts;

use App\Events\DraftPicksSynced;
use App\Events\DraftSyncStopped;
use App\Jobs\SyncDraftPicksJob;
use App\Models\Draft;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Throwable;

/**
 * Poll the platform for picks in the foreground, for the length of a draft.
 *
 * The same loop the queued job runs, held open by a terminal instead. The job
 * needs a worker and the room needs Reverb; one operator at a laptop on draft
 * night has neither, and a draft is the worst time to find that out.
 */
class SyncDraftPicksCommand extends Command
{
    protected $signature = 'drafts:sync
        { draft            : The draft to pull picks for }
        { --interval=10    : Seconds between polls }
        { --once           : Poll once and stop }
    ';

    protected $description = 'Pull picks from the league\'s platform while a draft is live';

    public function handle(): int
    {
        $draft = Draft::with('league')->find($this->argument('draft'));

        if (!$draft instanceof Draft) {
            $this->error('Draft not found.');

            return self::FAILURE;
        }

        $interval = max(1, (int) $this->option('interval'));

        $this->info(sprintf(
            'Polling %s for %s every %ds. Ctrl-C to stop.',
            $draft->league->platform,
            $draft->league->name,
            $interval,
        ));

        $failures = 0;

        while (true) {
            try {
                $result = SyncDraftPicksJob::sync($draft);

                $failures = 0;
            } catch (Throwable $e) {
                $failures++;

                $this->error('Poll failed (' . $failures . '/' . SyncDraftPicksJob::MAX_FAILURES . '): ' . $e->getMessage());

                if ($failures >= SyncDraftPicksJob::MAX_FAILURES) {
                    DraftSyncStopped::dispatch($draft, 'failed', $e->getMessage());

                    return self::FAILURE;
                }

                $result = null;
            }

            if ($result !== null && $this->report($draft, $result)) {
                return self::SUCCESS;
            }

            if ($this->option('once')) {
                return self::SUCCESS;
            }

            sleep($interval);
        }
    }

    /**
     * Say what the poll changed, and answer whether the draft is over.
     *
     * @param array<string, mixed> $result
     */
    private function report(Draft $draft, array $result): bool
    {
        $created = count(Arr::get($result, 'created', []));
        $updated = (int) Arr::get($result, 'updated', 0);
        $skipped = (int) Arr::get($result, 'skipped', 0);
        $completed = (bool) Arr::get($result, 'is_completed');

        // A quiet poll is the normal case, so the room is only disturbed when
        // the board actually changed.
        if ($created > 0 || $updated > 0 || $skipped > 0) {
            $this->line(sprintf(
                '[%s] %d new, %d updated, %d unmatched',
                now()->format('H:i:s'),
                $created,
                $updated,
                $skipped,
            ));

            DraftPicksSynced::dispatch($draft, $created, $updated, $skipped, $completed);
        }

        if (!$completed) {
            return false;
        }

        $draft->update(['is_completed' => true, 'is_active' => false]);

        DraftSyncStopped::dispatch($draft, 'completed');

        $this->info('Draft complete.');

        return true;
    }
}
