<?php

namespace App\Http\Controllers;

use App\Models\League;
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
    public function show(League $league)
    {
        $league->load([
            'creator',
            'draft.picks',
            'members' => [
                'rosters.player' => ['team', 'position'],
                'user',
            ],
            'settings',
        ]);

        return Inertia::render('leagues/show', [
            'league' => $league,
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
