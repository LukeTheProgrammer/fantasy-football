<?php

namespace App\Http\Controllers;

use App\Jobs\SyncLeagueJob;
use App\Models\League;
use Illuminate\Http\RedirectResponse;

/**
 * Re-pull a league from its platform on demand.
 */
class LeagueSyncController extends Controller
{
    public function store(League $league): RedirectResponse
    {
        $this->authorize('update', $league);

        SyncLeagueJob::start($league);

        return back()->with('success', 'Syncing ' . $league->name . ' from ' . $league->platform . '.');
    }
}
