<?php

use App\Http\Controllers\Api\ApiTeamController;
use App\Http\Controllers\Api\ApiPlayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::apiResource('teams', ApiTeamController::class)->only(['index', 'show']);
Route::apiResource('players', ApiPlayerController::class)->only(['index', 'show']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Protected team routes for create, update, delete
    // Route::apiResource('teams', ApiTeamController::class)->except(['index', 'show']);
    // Route::apiResource('players', ApiPlayerController::class)->except(['index', 'show']);
});
