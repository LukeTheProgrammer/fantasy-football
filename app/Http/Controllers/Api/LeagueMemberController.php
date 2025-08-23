<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\LeagueMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeagueMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'league_id' => 'required|exists:leagues,id'
        ]);
        
        $league = League::findOrFail($request->league_id);
        
        // Check if user is a member of this league
        if (!$league->members()->where('user_id', Auth::id())->exists() && !$league->is_public) {
            return response()->json(['message' => 'You do not have access to this league'], 403);
        }
        
        $members = $league->members()->with('user')->get();
        
        return response()->json($members);
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
            'league_id' => 'required|exists:leagues,id',
            'user_id' => 'required|exists:users,id',
            'team_name' => 'required|string|max:255',
            'team_logo' => 'nullable|string',
            'is_admin' => 'boolean',
        ]);
        
        $league = League::findOrFail($validated['league_id']);
        
        // Check if user is an admin of this league
        $membership = $league->members()->where('user_id', Auth::id())->first();
        if (!$membership || !$membership->is_admin) {
            return response()->json(['message' => 'You do not have permission to add members to this league'], 403);
        }
        
        // Check if league is full
        if ($league->members()->count() >= $league->max_teams) {
            return response()->json(['message' => 'This league is full'], 422);
        }
        
        // Check if user is already a member
        if ($league->members()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['message' => 'This user is already a member of this league'], 422);
        }
        
        $member = $league->members()->create([
            'user_id' => $validated['user_id'],
            'team_name' => $validated['team_name'],
            'team_logo' => $validated['team_logo'] ?? null,
            'is_admin' => $validated['is_admin'] ?? false,
        ]);
        
        return response()->json($member->load('user'), 201);
    }

    /**
     * Display the specified resource.
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $member = LeagueMember::with(['user', 'league'])->findOrFail($id);
        
        // Check if user is a member of this league
        if (!$member->league->members()->where('user_id', Auth::id())->exists() && !$member->league->is_public) {
            return response()->json(['message' => 'You do not have access to this league member'], 403);
        }
        
        return response()->json($member);
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
        $member = LeagueMember::with('league')->findOrFail($id);
        
        // Check if user is an admin of this league or the member themselves
        $userMembership = $member->league->members()->where('user_id', Auth::id())->first();
        $isSelf = $member->user_id === Auth::id();
        $isAdmin = $userMembership && $userMembership->is_admin;
        
        if (!$isAdmin && !$isSelf) {
            return response()->json(['message' => 'You do not have permission to update this league member'], 403);
        }
        
        $validated = $request->validate([
            'team_name' => 'sometimes|string|max:255',
            'team_logo' => 'nullable|string',
            'draft_position' => 'nullable|integer|min:1',
            'is_admin' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);
        
        // Only admins can change admin status
        if (isset($validated['is_admin']) && !$isAdmin) {
            return response()->json(['message' => 'Only league admins can change admin status'], 403);
        }
        
        $member->update($validated);
        
        return response()->json($member->fresh(['user', 'league']));
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $member = LeagueMember::with('league')->findOrFail($id);
        
        // Check if user is an admin of this league or the member themselves
        $userMembership = $member->league->members()->where('user_id', Auth::id())->first();
        $isSelf = $member->user_id === Auth::id();
        $isAdmin = $userMembership && $userMembership->is_admin;
        
        if (!$isAdmin && !$isSelf) {
            return response()->json(['message' => 'You do not have permission to remove this league member'], 403);
        }
        
        // Prevent removing the last admin
        if ($member->is_admin && $member->league->members()->where('is_admin', true)->count() <= 1) {
            return response()->json(['message' => 'Cannot remove the last admin from the league'], 422);
        }
        
        $member->delete();
        
        return response()->json(['message' => 'League member removed successfully']);
    }
    
    /**
     * Update the draft position for a league member.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDraftPosition(Request $request, string $id)
    {
        $member = LeagueMember::with('league')->findOrFail($id);
        
        // Check if user is an admin of this league
        $userMembership = $member->league->members()->where('user_id', Auth::id())->first();
        if (!$userMembership || !$userMembership->is_admin) {
            return response()->json(['message' => 'You do not have permission to update draft positions'], 403);
        }
        
        $validated = $request->validate([
            'draft_position' => 'required|integer|min:1',
        ]);
        
        // Check if the draft position is already taken
        $existingMember = $member->league->members()
            ->where('draft_position', $validated['draft_position'])
            ->where('id', '!=', $member->id)
            ->first();
            
        if ($existingMember) {
            return response()->json(['message' => 'This draft position is already taken'], 422);
        }
        
        $member->update([
            'draft_position' => $validated['draft_position']
        ]);
        
        return response()->json($member->fresh(['user', 'league']));
    }
}
