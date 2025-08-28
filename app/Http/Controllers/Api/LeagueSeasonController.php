<?php

namespace App\Http\Controllers\Api;

use App\Facades\Action;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeagueSeasonCreateRequest;
use App\Http\Requests\LeagueSeasonUpdateRequest;
use App\Models\League;
use App\Models\LeagueSeason;
use Illuminate\Http\Request;

class LeagueSeasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(League $league)
    {
        $this->authorize('view', $league);

        return response()->json($league->seasons()->orderBy('year', 'desc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LeagueSeasonCreateRequest $request, League $league)
    {
        $validated = $request->validated();

        // Set all other seasons to inactive if this one is active
        if ($request->input('is_active', false)) {
            $league->seasons()->update(['is_active' => false]);
        }

        $season = $league->seasons()->create($validated);

        return response()->json($season, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(League $league, LeagueSeason $season)
    {
        $this->authorize('view', $league);

        if ($season->league_id !== $league->id) {
            return response()->json(['message' => 'Season does not belong to this league'], 404);
        }

        return response()->json($season);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LeagueSeasonUpdateRequest $request, League $league, LeagueSeason $season)
    {
        if ($season->league_id !== $league->id) {
            return response()->json(['message' => 'Season does not belong to this league'], 404);
        }

        $validated = $request->validated();

        // Set all other seasons to inactive if this one is active
        if ($request->has('is_active') && $request->input('is_active')) {
            $league->seasons()->where('id', '!=', $season->id)->update(['is_active' => false]);
        }

        $season->update($validated);

        return response()->json($season);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(League $league, LeagueSeason $season)
    {
        $this->authorize('update', $league);

        if ($season->league_id !== $league->id) {
            return response()->json(['message' => 'Season does not belong to this league'], 404);
        }

        // Check if the season has any drafts
        if ($season->draft()->exists()) {
            return response()->json(['message' => 'Cannot delete a season that has drafts'], 422);
        }

        // Update any seasons that had this as their previous season
        LeagueSeason::where('previous_season_id', $season->id)->update(['previous_season_id' => null]);

        $season->delete();

        return response()->json(null, 204);
    }
}
