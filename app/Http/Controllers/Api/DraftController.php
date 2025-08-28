<?php

namespace App\Http\Controllers\Api;

use App\Facades\Action;
use App\Http\Controllers\Controller;
use App\Http\Requests\DraftCreateRequest;
use App\Http\Requests\DraftUpdateRequest;
use App\Http\Requests\DraftPickCreateRequest;
use App\Models\Draft;
use App\Models\DraftPick;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueSeason;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class DraftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(League $league)
    {
        $this->authorize('view', $league);

        return response()->json($league->drafts()->with('leagueSeason')->orderBy('draft_date', 'desc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DraftCreateRequest $request, League $league)
    {
        $validated = $request->validated();

        // Check if the season already has a draft
        $season = LeagueSeason::find($validated['league_season_id']);
        if ($season->draft()->exists()) {
            return response()->json(['message' => 'This season already has a draft'], 422);
        }

        $draft = $league->drafts()->create([
            'league_season_id' => Arr::get($validated, 'league_season_id'),
            'draft_date' => Arr::get($validated, 'draft_date'),
            'draft_type' => Arr::get($validated, 'draft_type'),
            'auction_budget' => Arr::get($validated, 'auction_budget') ?? null,
            'time_per_pick' => Arr::get($validated, 'time_per_pick') ?? 90,
            'is_active' => false,
            'is_completed' => false,
        ]);

        // Generate draft order
        $this->generateDraftOrder($draft);

        return response()->json($draft->load('picks.leagueMember', 'picks.player'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(League $league, Draft $draft)
    {
        $this->authorize('view', $league);

        if ($draft->league_id !== $league->id) {
            return response()->json(['message' => 'Draft does not belong to this league'], 404);
        }

        return response()->json($draft->load('picks.leagueMember', 'picks.player', 'leagueSeason'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DraftUpdateRequest $request, League $league, Draft $draft)
    {
        if ($draft->league_id !== $league->id) {
            return response()->json(['message' => 'Draft does not belong to this league'], 404);
        }

        // Don't allow updates to completed drafts
        if ($draft->is_completed) {
            return response()->json(['message' => 'Cannot update a completed draft'], 422);
        }

        $validated = $request->validated();

        // Only one draft can be active at a time
        if ($request->has('is_active') && $request->input('is_active')) {
            $league->drafts()->where('id', '!=', $draft->id)->update(['is_active' => false]);
        }

        $draft->update($validated);

        return response()->json($draft->load('picks.leagueMember', 'picks.player'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(League $league, Draft $draft)
    {
        $this->authorize('update', $league);

        if ($draft->league_id !== $league->id) {
            return response()->json(['message' => 'Draft does not belong to this league'], 404);
        }

        // Don't allow deletion of completed drafts
        if ($draft->is_completed) {
            return response()->json(['message' => 'Cannot delete a completed draft'], 422);
        }

        // Delete all picks
        $draft->picks()->delete();

        // Delete the draft
        $draft->delete();

        return response()->json(null, 204);
    }

    /**
     * Make a draft pick.
     */
    public function makePick(DraftPickCreateRequest $request, League $league, Draft $draft)
    {
        if ($draft->league_id !== $league->id) {
            return response()->json(['message' => 'Draft does not belong to this league'], 404);
        }

        if (!$draft->is_active) {
            return response()->json(['message' => 'Draft is not active'], 422);
        }

        if ($draft->is_completed) {
            return response()->json(['message' => 'Draft is already completed'], 422);
        }

        $validated = $request->validated();

        // Get the current pick
        $currentPick = $draft->picks()
            ->where('pick_number', $draft->current_pick)
            ->first();

        if (!$currentPick) {
            return response()->json(['message' => 'No current pick found'], 422);
        }

        // Update the pick with the player
        $currentPick->update([
            'player_id' => $validated['player_id'],
            'amount' => $draft->draft_type === 'auction' ? $validated['amount'] : null,
            'is_keeper' => $validated['is_keeper'] ?? false,
            'previous_year_cost' => $validated['previous_year_cost'] ?? null,
            'pick_time' => now(),
        ]);

        // Move to the next pick
        $nextPick = $draft->picks()
            ->whereNull('player_id')
            ->orderBy('pick_number')
            ->first();

        if ($nextPick) {
            $draft->update([
                'current_pick' => $nextPick->pick_number,
                'current_round' => $nextPick->round,
            ]);
        } else {
            // Draft is complete
            $draft->update([
                'is_completed' => true,
                'is_active' => false,
            ]);
        }

        return response()->json($draft->load('picks.leagueMember', 'picks.player'));
    }

    /**
     * Generate the draft order.
     */
    private function generateDraftOrder(Draft $draft)
    {
        // Get all league members
        $members = LeagueMember::where('league_id', $draft->league_id)->get();

        if ($members->isEmpty()) {
            return;
        }

        // Randomize the order
        $memberIds = $members->pluck('id')->shuffle();

        // Get the roster size from league settings
        $league = League::find($draft->league_id);
        $rosterSize = $league->settings->roster_size ?? 16;

        // Generate picks based on draft type
        if ($draft->draft_type === 'snake') {
            $this->generateSnakeDraftPicks($draft, $memberIds, $rosterSize);
        } else {
            $this->generateAuctionDraftPicks($draft, $memberIds, $rosterSize);
        }

        // Set the current pick
        $draft->update([
            'current_pick' => 1,
            'current_round' => 1,
        ]);
    }

    /**
     * Generate snake draft picks.
     */
    private function generateSnakeDraftPicks(Draft $draft, $memberIds, $rosterSize)
    {
        $totalMembers = count($memberIds);
        $pickNumber = 1;

        for ($round = 1; $round <= $rosterSize; $round++) {
            // For even rounds, reverse the order (snake draft)
            $roundMemberIds = $round % 2 === 0
                ? $memberIds->reverse()->values()
                : $memberIds;

            foreach ($roundMemberIds as $index => $memberId) {
                DraftPick::create([
                    'draft_id' => $draft->id,
                    'league_member_id' => $memberId,
                    'pick_number' => $pickNumber++,
                    'round' => $round,
                ]);
            }
        }
    }

    /**
     * Generate auction draft picks.
     */
    private function generateAuctionDraftPicks(Draft $draft, $memberIds, $rosterSize)
    {
        $totalMembers = count($memberIds);
        $pickNumber = 1;

        // For auction drafts, we just create placeholder picks
        // The actual order doesn't matter as much
        foreach ($memberIds as $memberId) {
            for ($i = 0; $i < $rosterSize; $i++) {
                DraftPick::create([
                    'draft_id' => $draft->id,
                    'league_member_id' => $memberId,
                    'pick_number' => $pickNumber++,
                    'round' => $i + 1,
                ]);
            }
        }
    }
}
