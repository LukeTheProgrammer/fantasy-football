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
        $leagues = Auth::user()->leagues()->with('settings')->get();

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
        $validated = $request->validated();

        $league = Action::model(League::class)->create(
            creator: $request->user(),
            data: [
                'name'        => Arr::get($validated, 'name'),
                'slug'        => Str::slug(Arr::get($validated, 'name')),
                'description' => Arr::get($validated, 'description'),
                'team_count'  => Arr::get($validated, 'team_count'),
                'is_public'   => Arr::get($validated, 'is_public', false),
                'join_code'   => Str::upper(Str::random(8)),
                'draft_type'  => Arr::get($validated, 'draft_type'),
                'draft_date'  => Arr::get($validated, 'draft_date'),
                'is_active'   => true,
            ]
        );

        Action::model(LeagueSettings::class)->create(
            league: $league,
            data: [
                'roster_positions'            => Arr::get($validated, 'settings.roster_positions', []),
                'roster_size'                 => Arr::get($validated, 'settings.roster_size', 16),
                'starters_count'              => Arr::get($validated, 'settings.starters_count', 9),
                'bench_count'                 => Arr::get($validated, 'settings.bench_count', 7),
                'ir_spots'                    => Arr::get($validated, 'settings.ir_spots', 1),
                'passing_points_per_yard'     => Arr::get($validated, 'settings.passing_points_per_yard', 0.04),
                'passing_td_points'           => Arr::get($validated, 'settings.passing_td_points', 4.0),
                'interception_points'         => Arr::get($validated, 'settings.interception_points', -2.0),
                'rushing_points_per_yard'     => Arr::get($validated, 'settings.rushing_points_per_yard', 0.1),
                'rushing_td_points'           => Arr::get($validated, 'settings.rushing_td_points', 6.0),
                'receiving_points_per_yard'   => Arr::get($validated, 'settings.receiving_points_per_yard', 0.1),
                'receiving_td_points'         => Arr::get($validated, 'settings.receiving_td_points', 6.0),
                'reception_points'            => Arr::get($validated, 'settings.reception_points', 0.0),
                'fumble_lost_points'          => Arr::get($validated, 'settings.fumble_lost_points', -2.0),
                'two_point_conversion_points' => Arr::get($validated, 'settings.two_point_conversion_points', 2.0),
            ],
        );

        Action::model(LeagueMember::class)->create(
            league: $league,
            user: $request->user(),
            data: [
                'team_name' => $request->user()->name . "'s Team",
                'is_admin'  => true,
            ],
        );

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
