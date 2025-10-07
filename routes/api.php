<?php

use App\Http\Controllers\Api\DraftController;
use App\Http\Controllers\Api\LeagueController;
use App\Http\Controllers\Api\LeagueMemberController;
use App\Http\Controllers\Api\LeagueSeasonController;
use App\Http\Controllers\Api\LeagueSettingsController;
use App\Http\Controllers\Api\PlayerAliasController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('players/search', [PlayerController::class, 'search']);

    // Protected team routes for create, update, delete
    Route::apiResource('teams', TeamController::class)->except(['create', 'show']);
    Route::apiResource('players', PlayerController::class)->except(['index', 'show']);
    Route::apiResource('player-aliases', PlayerAliasController::class)->only(['update']);

    // League management routes
    Route::apiResource('leagues', LeagueController::class);
    Route::apiResource('league-settings', LeagueSettingsController::class)->except(['index', 'store', 'destroy']);
    Route::apiResource('league-members', LeagueMemberController::class);

    // League season routes
    // Route::apiResource('leagues.seasons', LeagueSeasonController::class)->parameters([
    //     'seasons' => 'season'
    // ]);

    // Custom league routes
    Route::post('leagues/join', [LeagueController::class, 'join']);
    Route::patch('league-members/{id}/draft-position', [LeagueMemberController::class, 'updateDraftPosition']);


    // Draft routes
    Route::apiResource('leagues.drafts', DraftController::class);
    Route::post('leagues/{league}/drafts/{draft}/picks', [DraftController::class, 'makePick']);
    Route::get('leagues/{league}/drafts/{draft}/board', [DraftController::class, 'board']);
});
