<?php

namespace App\Http\Controllers\Api;

use App\Facades\Action;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerUpdateRequest;
use App\Models\Player;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Player::all());
    }

    /**
     * Searches for players.
     */
    public function search(Request $request)
    {
        $search = $request->input('search');

        return response()->json(
            Player::nameLike($search)->get()
        );
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
    public function show(Player $player)
    {
        return response()->json($player);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PlayerUpdateRequest $request, Player $player)
    {
        Action::model(Player::class)->update($player, $request->validated());

        return response()->json($player->refresh()->load(['position', 'team']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Player $player)
    {
        //
    }
}
