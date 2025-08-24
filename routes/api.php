<?php

use App\Http\Controllers\Api\ApiPlayerController;
use App\Http\Controllers\Api\ApiTeamController;
use App\Http\Controllers\Api\LeagueController;
use App\Http\Controllers\Api\LeagueSettingsController;
use App\Http\Controllers\Api\LeagueMemberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public API routes
// Route::apiResource('teams', ApiTeamController::class)->only(['index', 'show']);
Route::apiResource('players', ApiPlayerController::class)->only(['index', 'show']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('test', function () {
        return response()->json(['message' => 'Hello, world!']);
    });

    // Protected team routes for create, update, delete
    Route::apiResource('teams', ApiTeamController::class)->except(['create', 'show']);
    Route::apiResource('players', ApiPlayerController::class)->except(['index', 'show']);
    
    // League management routes
    Route::apiResource('leagues', LeagueController::class);
    Route::apiResource('league-settings', LeagueSettingsController::class)->except(['index', 'store', 'destroy']);
    Route::apiResource('league-members', LeagueMemberController::class);
    
    // Custom league routes
    Route::post('leagues/join', [LeagueController::class, 'join']);
    Route::patch('league-members/{id}/draft-position', [LeagueMemberController::class, 'updateDraftPosition']);
});
