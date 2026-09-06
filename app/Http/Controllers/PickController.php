<?php

namespace App\Http\Controllers;

use App\Facades\Pick as PickFacade;
use App\Http\Requests\PickStoreRequest;
use App\Models\Draft;
use App\Models\DraftPick;
use Illuminate\Http\RedirectResponse;

class PickController extends Controller
{
    /**
     * Record the pick that is on the clock.
     */
    public function store(PickStoreRequest $request, Draft $draft): RedirectResponse
    {
        $validated = $request->validated();

        $clock = PickFacade::onTheClock($draft);
        $slot = $clock['current'];

        if ($slot === null) {
            return back()->withErrors(['player_id' => 'Every pick in this draft has been made.']);
        }

        // Whoever the order says is up owns the pick, unless the room names a
        // team on purpose.
        $memberId = $validated['league_member_id'] ?? $slot['league_member_id'];

        if ($memberId === null) {
            return back()->withErrors(['player_id' => 'The team on the clock is not a member of this league.']);
        }

        $draft->picks()->create([
            'league_member_id'    => $memberId,
            'player_id'           => $validated['player_id'],
            'round'               => $slot['round'],
            'pick_number'         => $slot['pick_number'],
            'overall_pick_number' => $slot['overall_pick_number'],
            'amount'              => null,
            'is_keeper'           => false,
        ]);

        return back()->with('success', 'Pick recorded.');
    }

    /**
     * Pass on the slot that is on the clock without taking anybody.
     *
     * The draft ends when a roster is full rather than when the order runs
     * out, so a team holding more slots than it has room for has to be able
     * to give one up. The slot is used either way: the clock reads the order
     * for the first slot with nothing against it, so a pass is recorded as a
     * pick with no player rather than by moving the clock past it.
     */
    public function skip(Draft $draft): RedirectResponse
    {
        $this->authorize('record', $draft);

        $slot = PickFacade::onTheClock($draft)['current'];

        if ($slot === null) {
            return back()->withErrors(['player_id' => 'Every pick in this draft has been made.']);
        }

        if ($slot['league_member_id'] === null) {
            return back()->withErrors(['player_id' => 'The team on the clock is not a member of this league.']);
        }

        $draft->picks()->create([
            'league_member_id'    => $slot['league_member_id'],
            'player_id'           => null,
            'round'               => $slot['round'],
            'pick_number'         => $slot['pick_number'],
            'overall_pick_number' => $slot['overall_pick_number'],
            'amount'              => null,
            'is_keeper'           => false,
        ]);

        return back()->with('success', 'Pick skipped.');
    }

    /**
     * Undo the pick, putting its slot back on the clock.
     */
    public function destroy(Draft $draft, DraftPick $pick): RedirectResponse
    {
        $this->authorize('record', $draft);

        if ($pick->draft_id !== $draft->id) {
            abort(404, 'Pick does not belong to this draft');
        }

        $pick->delete();

        return back()->with('success', 'Pick undone.');
    }
}
