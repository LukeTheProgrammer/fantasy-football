<?php

namespace App\Http\Controllers;

use App\Models\PlayerAlias;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlayerAliasesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('players/aliases', [
            'aliases' => PlayerAlias::with(['player'])->orderBy('name')->get(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PlayerAlias $playerAlias)
    {
        return response()->json($playerAlias);
    }
}
