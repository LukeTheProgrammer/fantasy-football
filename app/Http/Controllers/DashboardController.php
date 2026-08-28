<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeagueShowResource;
use App\Models\League;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leagues = League::forUser(Auth::user())
            ->whereIn('id', League::selectRaw('MAX(id)')->groupBy('platform_id'))
            ->with(['draft', 'members'])
            ->get();

        return Inertia::render('DashboardPage', [
            'leagues' => $leagues,
        ]);

        // $leagues = League::forUser(Auth::user())
        //     ->whereIn('id', League::selectRaw('MAX(id)')->groupBy('platform_id'))
        //     ->with([
        //         'creator',
        //         'draft',
        //         'matchups' => [
        //             'homeTeam',
        //             'awayTeam',
        //         ],
        //         'members',
        //     ])
        //     ->get();

        // return Inertia::render('DashboardPage', [
        //     'leagues' => LeagueShowResource::collection($leagues),
        // ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        return response()->json($team);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        //
    }
}
