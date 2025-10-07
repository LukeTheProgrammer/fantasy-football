<?php

namespace App\Http\Controllers;

use App\Facades\Action;
use App\Http\Requests\PlayerUpdateRequest;
use App\Models\Player;
use App\Models\PlayerAlias;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlayersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('players/index', [
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
