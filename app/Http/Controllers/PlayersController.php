<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Position;
use App\Models\Team;
use Inertia\Inertia;

class PlayersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('players/PlayersIndexPage', [
            'players' => Player::with(['aliases', 'position', 'team'])->orderBy('full_name')->get(),
            'teams' => Team::noFA()->orderBy('location')->get(),
            'positions' => Position::orderBy('name')->get(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Player $player)
    {
        return response()->json($player);
    }
}
