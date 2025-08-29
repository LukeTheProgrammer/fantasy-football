<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\LeagueController;
// use App\Https\Controllers\LeagueSeasonController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // League model
    Route::prefix('leagues')->name('leagues.')->group(function () {
        Route::get('/', [LeagueController::class, 'index'])->name('index');
        Route::get('/create', [LeagueController::class, 'create'])->name('create');
        Route::get('/{league}/edit', [LeagueController::class, 'edit'])->name('edit');
        Route::get('/{league}', [LeagueController::class, 'show'])->name('show');
    });

    // LeagueSeason model
    // Route::prefix('seasons')->name('seasons.')->group(function () {
    //     Route::get('/', [LeagueSeasonController::class, 'index'])->name('index');
    //     Route::get('/create', [LeagueSeasonController::class, 'create'])->name('create');
    //     Route::get('/{season}/edit', [LeagueSeasonController::class, 'edit'])->name('edit');
    //     Route::get('/{season}', [LeagueSeasonController::class, 'show'])->name('show');
    // });

    // Draft model
    Route::prefix('drafts')->name('drafts.')->group(function () {
        Route::get('/', [DraftController::class, 'index'])->name('index');
        Route::get('/create', [DraftController::class, 'create'])->name('create');
        Route::get('/{draft}', [DraftController::class, 'show'])->name('show');
        Route::get('/{draft}/edit', [DraftController::class, 'edit'])->name('edit');
        Route::get('/{draft}/board', [DraftController::class, 'board'])->name('board');
        Route::get('/{draft}/results', [DraftController::class, 'results'])->name('results');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
