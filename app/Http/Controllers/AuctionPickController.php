<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuctionPickStoreRequest;
use App\Models\Draft;
use App\Models\DraftPick;
use Illuminate\Http\RedirectResponse;

class AuctionPickController extends Controller
{
    /**
     * Record a player being sold.
     */
    public function store(AuctionPickStoreRequest $request, Draft $draft): RedirectResponse
    {
        $validated = $request->validated();

        // An auction has no fixed pick order, so a sale is simply the next one
        // recorded.
        $sold = $draft->picks()->count();

        $draft->picks()->create([
            'league_member_id'    => $validated['league_member_id'],
            'player_id'           => $validated['player_id'],
            'amount'              => $validated['amount'],
            'round'               => 0,
            'pick_number'         => $sold + 1,
            'overall_pick_number' => $sold + 1,
        ]);

        return back()->with('success', 'Sold.');
    }

    /**
     * Undo a recorded sale.
     */
    public function destroy(Draft $draft, DraftPick $pick): RedirectResponse
    {
        $this->authorize('record', $draft);

        if ($pick->draft_id !== $draft->id) {
            abort(404, 'Pick does not belong to this draft');
        }

        $pick->delete();

        return back()->with('success', 'Sale undone.');
    }
}
