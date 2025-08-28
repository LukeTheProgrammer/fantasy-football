<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // League management routes
    Route::get('leagues', [LeagueController::class, 'index'])->name('leagues.index');
    Route::get('leagues/create', [LeagueController::class, 'create'])->name('leagues.create');
    Route::get('leagues/{league}/edit', [LeagueController::class, 'edit'])->name('leagues.edit');
    Route::get('leagues/{league}', [LeagueController::class, 'show'])->name('leagues.show');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
