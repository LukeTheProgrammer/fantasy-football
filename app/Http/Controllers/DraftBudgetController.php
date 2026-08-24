<?php

namespace App\Http\Controllers;

use App\Http\Requests\DraftBudgetUpdateRequest;
use App\Models\Draft;
use App\Models\DraftBudget;
use App\Models\LeagueMember;
use Illuminate\Http\RedirectResponse;

class DraftBudgetController extends Controller
{
    /**
     * Save the signed in user's plan for their own team.
     */
    public function update(DraftBudgetUpdateRequest $request, Draft $draft): RedirectResponse
    {
        $member = $draft->league->members->firstWhere('user_id', $request->user()->id);

        if (!$member instanceof LeagueMember) {
            abort(403, 'You do not have a team in this league');
        }

        DraftBudget::updateOrCreate(
            ['draft_id' => $draft->id, 'league_member_id' => $member->id],
            ['allocations' => $request->allocations()],
        );

        return back()->with('success', 'Budget saved.');
    }
}
