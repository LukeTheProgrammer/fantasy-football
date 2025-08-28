<?php

use App\Http\Controllers\Api\LeagueController;
use App\Http\Controllers\Api\LeagueMemberController;
use App\Http\Controllers\Api\LeagueSettingsController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Protected team routes for create, update, delete
    Route::apiResource('teams', TeamController::class)->except(['create', 'show']);
    Route::apiResource('players', PlayerController::class)->except(['index', 'show']);

    // League management routes
    Route::apiResource('leagues', LeagueController::class);
    Route::apiResource('league-settings', LeagueSettingsController::class)->except(['index', 'store', 'destroy']);
    Route::apiResource('league-members', LeagueMemberController::class);

    // Custom league routes
    Route::post('leagues/join', [LeagueController::class, 'join']);
    Route::patch('league-members/{id}/draft-position', [LeagueMemberController::class, 'updateDraftPosition']);
});
