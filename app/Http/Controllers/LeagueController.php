<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\NflGame;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeagueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('leagues/index', [
            'leagues' => League::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create()
    {
        return Inertia::render('leagues/create');
    }

    /**
     * Display the specified resource.
     */
    public function show(League $league, Request $request)
    {
        $league->load([
            'creator',
            'draft.picks',
            'fantasyPointsWeeks',
            'matchups' => ['homeTeam', 'awayTeam'],
            'members' => [
                'rosters.player' => [
                    'position',
                    'team',
                ],
                'user',
            ],
            'settings',
        ]);

        return Inertia::render('leagues/show', [
            'league' => $league,
            'nfl_games' => NflGame::forYear(2025)
                ->with(['awayTeam', 'homeTeam'])
                ->get(),
        ]);
    }

    /**
     * Display the specified resource to edit.
     */
    public function edit(League $league)
    {
        $league->load(['creator', 'settings', 'members.user']);

        return Inertia::render('leagues/edit', [
            'league' => $league,
        ]);
    }
}
