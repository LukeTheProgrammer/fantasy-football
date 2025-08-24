<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\LeagueSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LeagueController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Get leagues the user is a member of
        $leagues = Auth::user()->leagues()->with('settings')->get();
        
        return response()->json($leagues);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_teams' => 'required|integer|min:2|max:32',
            'is_public' => 'boolean',
            'draft_type' => 'required|string|in:snake,auction',
            'draft_date' => 'nullable|date',
            
            // League settings validation
            'settings.roster_positions' => 'required|array',
            'settings.roster_size' => 'required|integer|min:1',
            'settings.starters_count' => 'required|integer|min:1',
            'settings.bench_count' => 'required|integer|min:0',
            'settings.ir_spots' => 'integer|min:0',
            
            // Scoring settings
            'settings.passing_yards_per_point' => 'required|numeric',
            'settings.passing_td_points' => 'required|numeric',
            'settings.interception_points' => 'required|numeric',
            'settings.rushing_yards_per_point' => 'required|numeric',
            'settings.rushing_td_points' => 'required|numeric',
            'settings.receiving_yards_per_point' => 'required|numeric',
            'settings.receiving_td_points' => 'required|numeric',
            'settings.reception_points' => 'required|numeric',
            'settings.fumble_lost_points' => 'required|numeric',
            'settings.two_point_conversion_points' => 'required|numeric',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create the league
            $league = new League([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
                'description' => $validated['description'] ?? null,
                'created_by' => Auth::id(),
                'max_teams' => $validated['max_teams'],
                'is_public' => $validated['is_public'] ?? false,
                'join_code' => Str::upper(Str::random(8)),
                'draft_type' => $validated['draft_type'],
                'draft_date' => $validated['draft_date'] ?? null,
                'is_active' => true,
            ]);
            
            $league->save();
            
            // Create league settings
            $settings = new LeagueSettings([
                'league_id' => $league->id,
                'roster_positions' => $validated['settings']['roster_positions'],
                'roster_size' => $validated['settings']['roster_size'],
                'starters_count' => $validated['settings']['starters_count'],
                'bench_count' => $validated['settings']['bench_count'],
                'ir_spots' => $validated['settings']['ir_spots'] ?? 0,
                'passing_yards_per_point' => $validated['settings']['passing_yards_per_point'],
                'passing_td_points' => $validated['settings']['passing_td_points'],
                'interception_points' => $validated['settings']['interception_points'],
                'rushing_yards_per_point' => $validated['settings']['rushing_yards_per_point'],
                'rushing_td_points' => $validated['settings']['rushing_td_points'],
                'receiving_yards_per_point' => $validated['settings']['receiving_yards_per_point'],
                'receiving_td_points' => $validated['settings']['receiving_td_points'],
                'reception_points' => $validated['settings']['reception_points'],
                'fumble_lost_points' => $validated['settings']['fumble_lost_points'],
                'two_point_conversion_points' => $validated['settings']['two_point_conversion_points'],
            ]);
            
            $league->settings()->save($settings);
            
            // Add the creator as a league member and admin
            $league->members()->create([
                'user_id' => Auth::id(),
                'team_name' => Auth::user()->name . "'s Team",
                'is_admin' => true,
            ]);
            
            DB::commit();
            
            return response()->json($league->load('settings', 'members'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $league = League::with(['settings', 'members.user'])
            ->findOrFail($id);
            
        // Check if user is a member of this league
        if (!$league->members()->where('user_id', Auth::id())->exists() && !$league->is_public) {
            return response()->json(['message' => 'You do not have access to this league'], 403);
        }
        
        return response()->json($league);
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
        $league = League::findOrFail($id);
        
        // Check if user is an admin of this league
        $membership = $league->members()->where('user_id', Auth::id())->first();
        if (!$membership || !$membership->is_admin) {
            return response()->json(['message' => 'You do not have permission to update this league'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'max_teams' => 'sometimes|integer|min:2|max:32',
            'is_public' => 'sometimes|boolean',
            'draft_type' => 'sometimes|string|in:snake,auction',
            'draft_date' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $league->update($validated);
        
        return response()->json($league->fresh(['settings', 'members.user']));
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $league = League::findOrFail($id);
        
        // Check if user is the creator of this league
        if ($league->created_by !== Auth::id()) {
            return response()->json(['message' => 'Only the league creator can delete this league'], 403);
        }
        
        $league->delete();
        
        return response()->json(['message' => 'League deleted successfully']);
    }
    
    /**
     * Join a league using a join code.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'join_code' => 'required|string|size:8',
            'team_name' => 'required|string|max:255',
        ]);
        
        $league = League::where('join_code', $validated['join_code'])->first();
        
        if (!$league) {
            throw ValidationException::withMessages([
                'join_code' => ['Invalid join code'],
            ]);
        }
        
        // Check if league is full
        if ($league->members()->count() >= $league->max_teams) {
            throw ValidationException::withMessages([
                'join_code' => ['This league is full'],
            ]);
        }
        
        // Check if user is already a member
        if ($league->members()->where('user_id', Auth::id())->exists()) {
            throw ValidationException::withMessages([
                'join_code' => ['You are already a member of this league'],
            ]);
        }
        
        // Add user to league
        $membership = $league->members()->create([
            'user_id' => Auth::id(),
            'team_name' => $validated['team_name'],
            'is_admin' => false,
        ]);
        
        return response()->json($league->load('settings', 'members.user'), 201);
    }
}
