<?php

namespace App\Http\Controllers;

use App\Events\DraftSyncStopped;
use App\Jobs\SyncDraftPicksJob;
use App\Models\Draft;
use Illuminate\Http\RedirectResponse;

/**
 * Start and stop the loop that pulls picks from the league's platform while a
 * draft is live.
 */
class DraftSyncController extends Controller
{
    public function store(Draft $draft): RedirectResponse
    {
        $this->authorize('record', $draft);

        $draft->update(['is_active' => true]);

        SyncDraftPicksJob::start($draft);

        return back()->with('success', 'Pulling picks from ' . $draft->league->platform . '.');
    }

    public function destroy(Draft $draft): RedirectResponse
    {
        $this->authorize('record', $draft);

        $draft->update(['is_active' => false]);

        SyncDraftPicksJob::stop($draft);

        DraftSyncStopped::dispatch($draft, 'stopped');

        return back()->with('success', 'Stopped pulling picks.');
    }
}
