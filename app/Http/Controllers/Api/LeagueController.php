<?php

namespace App\Http\Controllers\Api;

use App\Facades\Action;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeagueCreateRequest;
use App\Http\Requests\LeagueUpdateRequest;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\LeagueSettings;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LeagueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        // Get leagues the user is a member of
        // $leagues = Auth::user()->leagues()->with('settings')->get();

        $leagues = League::with(['settings', 'members.user'])->get();

        return response()->json($leagues);
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     *
     * @return JsonResponse
     */
    public function show(string $id)
    {
        $league = League::with(['settings', 'members.user'])->findOrFail($id);

        // Check if user is a member of this league
        if (! $league->userIsMember(Auth::user()) && ! $league->is_public) {
            return response()->json(['message' => 'You do not have access to this league'], 403);
        }

        return response()->json($league);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param LeagueCreateRequest $request
     *
     * @return JsonResponse
     */
    public function store(LeagueCreateRequest $request)
    {
        try {
            DB::beginTransaction();

            $league = Action::model(League::class)->create(
                creator: $request->user(),
                data: $request->validated(),
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Failed to create league', 'error' => $e->getMessage()], 500);
        }

        return response()->json($league->load('settings', 'members'), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param League $league
     * @param LeagueUpdateRequest $request
     *
     * @return JsonResponse
     */
    public function update(League $league, LeagueUpdateRequest $request)
    {
        // Check if user is an admin of this league
        $membership = $league->members()->where('user_id', $request->user()->id)->first();

        if (!$membership || !$membership->is_admin) {
            return response()->json(['message' => 'You do not have permission to update this league'], 403);
        }

        $validated = $request->validated();

        $league = Action::model(League::class)->update(
            league: $league,
            data: array_filter(Arr::only($validated, [
                'name',
                'description',
                'team_count',
                'is_public',
                'join_code',
                'draft_type',
                'draft_date',
                'is_active',
            ])),
        );

        Action::model(LeagueSettings::class)->update(
            settings: $league->settings,
            data: array_filter(Arr::get($validated, 'settings', [])),
        );

        foreach (Arr::get($validated, 'members', []) as $memberData) {
            try {
                $member = LeagueMember::findOrFail(Arr::get($memberData, 'id'));

                Action::model(LeagueMember::class)->update(
                    member: $member,
                    data: $memberData,
                );
            } catch (Exception $e) {
                return response()->json(['message' => 'Failed to update member'], 500);
            }
        }

        return response()->json($league->load('settings', 'members.user'), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     *
     * @return JsonResponse
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
     * @param Request $request
     *
     * @return JsonResponse
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
            'user_id'   => Auth::id(),
            'team_name' => $validated['team_name'],
            'is_admin'  => false,
        ]);

        return response()->json($league->load('settings', 'members.user'), 201);
    }
}
