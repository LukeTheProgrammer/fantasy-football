<?php

namespace App\Http\Controllers\Api;

use App\Facades\Action;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerMissingCreateRequest;
use App\Http\Requests\PlayerMissingUpdateRequest;
use App\Models\PlayerMissing;

class PlayerMissingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(PlayerMissing::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlayerMissingCreateRequest $request)
    {
        $playerMissing = Action::model(PlayerMissing::class)->upsert($request->validated());

        return response()->json($playerMissing);
    }

    /**
     * Display the specified resource.
     */
    public function show(PlayerMissing $playerMissing)
    {
        return response()->json($playerMissing);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function update(PlayerMissingUpdateRequest $request, PlayerMissing $playerMissing)
    {
        $playerMissing->update($request->validated());

        return response()->json($playerMissing->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlayerMissing $playerMissing)
    {
        $playerMissing->forceDelete();

        return response()->json();
    }
}
