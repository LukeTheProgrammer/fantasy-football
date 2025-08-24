<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\LeagueSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeagueSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * This method is not needed as settings are accessed through leagues
     */
    public function index()
    {
        return response()->json(['message' => 'Method not allowed'], 405);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * This method is not needed as settings are created with leagues
     */
    public function store(Request $request)
    {
        return response()->json(['message' => 'Method not allowed'], 405);
    }

    /**
     * Display the specified resource.
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $settings = LeagueSettings::with('league')->findOrFail($id);
        
        // Check if user is a member of this league
        if (!$settings->league->members()->where('user_id', Auth::id())->exists() && !$settings->league->is_public) {
            return response()->json(['message' => 'You do not have access to these league settings'], 403);
        }
        
        return response()->json($settings);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $settings = LeagueSettings::with('league')->findOrFail($id);
        
        // Check if user is an admin of this league
        $membership = $settings->league->members()->where('user_id', Auth::id())->first();
        if (!$membership || !$membership->is_admin) {
            return response()->json(['message' => 'You do not have permission to update these league settings'], 403);
        }
        
        $validated = $request->validate([
            'roster_positions' => 'sometimes|array',
            'roster_size' => 'sometimes|integer|min:1',
            'starters_count' => 'sometimes|integer|min:1',
            'bench_count' => 'sometimes|integer|min:0',
            'ir_spots' => 'sometimes|integer|min:0',
            'passing_yards_per_point' => 'sometimes|numeric',
            'passing_td_points' => 'sometimes|numeric',
            'interception_points' => 'sometimes|numeric',
            'rushing_yards_per_point' => 'sometimes|numeric',
            'rushing_td_points' => 'sometimes|numeric',
            'receiving_yards_per_point' => 'sometimes|numeric',
            'receiving_td_points' => 'sometimes|numeric',
            'reception_points' => 'sometimes|numeric',
            'fumble_lost_points' => 'sometimes|numeric',
            'two_point_conversion_points' => 'sometimes|numeric',
            'field_goal_0_39_points' => 'sometimes|numeric',
            'field_goal_40_49_points' => 'sometimes|numeric',
            'field_goal_50_plus_points' => 'sometimes|numeric',
            'extra_point_points' => 'sometimes|numeric',
            'defense_sack_points' => 'sometimes|numeric',
            'defense_interception_points' => 'sometimes|numeric',
            'defense_fumble_recovery_points' => 'sometimes|numeric',
            'defense_td_points' => 'sometimes|numeric',
            'defense_safety_points' => 'sometimes|numeric',
            'defense_points_allowed_tiers' => 'sometimes|array',
        ]);
        
        $settings->update($validated);
        
        return response()->json($settings->fresh());
    }

    /**
     * Remove the specified resource from storage.
     * 
     * This method is not allowed as settings should not be deleted separately from leagues
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        return response()->json(['message' => 'Method not allowed'], 405);
    }
}
