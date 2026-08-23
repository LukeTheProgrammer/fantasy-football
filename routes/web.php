<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\DraftRankingController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\PlayersController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// The app has no public landing page: signed in goes to the dashboard,
// everyone else goes to the login screen.
Route::get('/', fn () => redirect()->route(Auth::check() ? 'dashboard' : 'login'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('players', [PlayersController::class, 'index'])->name('players.index');

    // League model
    Route::prefix('leagues')->name('leagues.')->group(function () {
        Route::get('/', [LeagueController::class, 'index'])->name('index');
        Route::get('/create', [LeagueController::class, 'create'])->name('create');
        Route::get('/{league}/edit', [LeagueController::class, 'edit'])->name('edit');
        Route::get('/{league}', [LeagueController::class, 'show'])->name('show');
    });

    // Draft model
    Route::prefix('drafts')->name('drafts.')->group(function () {
        Route::get('/', [DraftController::class, 'index'])->name('index');
        Route::get('/create', [DraftController::class, 'create'])->name('create');
        Route::get('/{draft}', [DraftController::class, 'show'])->name('show');
        Route::get('/{draft}/edit', [DraftController::class, 'edit'])->name('edit');
        Route::get('/{draft}/results', [DraftController::class, 'results'])->name('results');
        Route::get('/{draft}/draft-room', [DraftController::class, 'draftRoom'])->name('draft-room');
    });

    // DraftRanking model
    Route::prefix('rankings')->name('rankings.')->group(function () {
        Route::get('/', [DraftRankingController::class, 'index'])->name('index');
        // Route::get('/create', [DraftRankingController::class, 'create'])->name('create');
        // Route::get('/{draftRanking}/edit', [DraftRankingController::class, 'edit'])->name('edit');
        // Route::get('/{draftRanking}', [DraftRankingController::class, 'show'])->name('show');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
