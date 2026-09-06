<?php

use App\Http\Controllers\AuctionPickController;
use App\Http\Controllers\AuctionPlayerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DraftBudgetController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\DraftRankingController;
use App\Http\Controllers\DraftSyncController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\LeagueSyncController;
use App\Http\Controllers\PickController;
use App\Http\Controllers\PickPlayerController;
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
        // Re-pull the league and its rosters from the platform.
        Route::post('/{league}/sync', [LeagueSyncController::class, 'store'])->name('sync.store');
    });

    // Draft model
    Route::prefix('drafts')->name('drafts.')->group(function () {
        Route::get('/', [DraftController::class, 'index'])->name('index');
        Route::get('/create', [DraftController::class, 'create'])->name('create');
        // A draft is addressed by its league and the year it was drafted for,
        // which is what the season picker switches between. The four digit
        // constraint keeps it clear of the sibling routes below.
        Route::get('/{league}/{season}', [DraftController::class, 'show'])->name('show')->where('season', '\d{4}');
        Route::get('/{draft}/edit', [DraftController::class, 'edit'])->name('edit');
        Route::get('/{draft}/results', [DraftController::class, 'results'])->name('results');
        Route::get('/{draft}/budgets', [DraftController::class, 'budgets'])->name('budgets');
        Route::get('/{draft}/draft-room', [DraftController::class, 'draftRoom'])->name('draft-room');

        // Everything known about one player, for the dialog in the room.
        Route::get('/{draft}/players/{player}', [AuctionPlayerController::class, 'show'])->name('players.show');

        // Recording what the room sees: an auction sale, or undoing one.
        Route::post('/{draft}/picks', [AuctionPickController::class, 'store'])->name('picks.store');
        // Pulling picks from ESPN while the draft is running.
        Route::post('/{draft}/sync', [DraftSyncController::class, 'store'])->name('sync.store');
        Route::delete('/{draft}/sync', [DraftSyncController::class, 'destroy'])->name('sync.destroy');
        Route::put('/{draft}/budget', [DraftBudgetController::class, 'update'])->name('budget.update');
        Route::patch('/{draft}/picks/{pick}', [AuctionPickController::class, 'update'])->name('picks.update');
        Route::delete('/{draft}/picks/{pick}', [AuctionPickController::class, 'destroy'])->name('picks.destroy');

        // A pick draft records whoever the order has on the clock, so it is a
        // different write from an auction sale.
        Route::post('/{draft}/board-picks', [PickController::class, 'store'])->name('board-picks.store');
        Route::post('/{draft}/board-picks/skip', [PickController::class, 'skip'])->name('board-picks.skip');
        Route::delete('/{draft}/board-picks/{pick}', [PickController::class, 'destroy'])->name('board-picks.destroy');
        // The profile behind a name in the pick room.
        Route::get('/{draft}/board-players/{player}', [PickPlayerController::class, 'show'])->name('board-players.show');
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
