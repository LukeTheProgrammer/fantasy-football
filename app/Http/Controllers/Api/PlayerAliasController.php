<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerAliasCreateRequest;
use App\Http\Requests\PlayerAliasUpdateRequest;
use App\Models\PlayerAlias;

class PlayerAliasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            PlayerAlias::with(['player'])->orderBy('name')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlayerAliasCreateRequest $request)
    {
        $playerAlias = PlayerAlias::create($request->validated());

        return response()->json($playerAlias->load('player'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PlayerAlias $playerAlias)
    {
        return response()->json($playerAlias->load('player'));
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
        $playerAlias->delete();

        return response()->json($playerAlias);
    }
}
