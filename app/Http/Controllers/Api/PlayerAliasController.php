<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerAliasUpdateRequest;
use App\Models\PlayerAlias;
use Illuminate\Http\Request;

class PlayerAliasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(PlayerAlias::all());
    }

    /**
     * Searches for players.
     */
    public function search(Request $request)
    {
        $search = $request->input('search');

        return response()->json(
            PlayerAlias::nameLike($search)->get()
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
    public function show(PlayerAlias $playerAlias)
    {
        return response()->json($playerAlias);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PlayerAliasUpdateRequest $request, PlayerAlias $playerAlias)
    {
        $playerAlias->update($request->validated());

        return response()->json($playerAlias->refresh()->load('player'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlayerAlias $playerAlias)
    {
        // $playerAlias->delete();

        // return response()->json($playerAlias);
    }
}
