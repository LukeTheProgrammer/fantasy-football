<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('leagues')->name('leagues.')->group(function () {
        // League management routes
        Route::get('/', [LeagueController::class, 'index'])->name('index');
        Route::get('/create', [LeagueController::class, 'create'])->name('create');
        Route::get('/{league}/edit', [LeagueController::class, 'edit'])->name('edit');
        Route::get('/{league}', [LeagueController::class, 'show'])->name('show');
    });

    // Draft management routes
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
