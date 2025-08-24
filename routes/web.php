<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // League management routes
    Route::get('leagues', function () {
        return Inertia::render('leagues/index');
    })->name('leagues.index');
    
    Route::get('leagues/create', function () {
        return Inertia::render('leagues/create');
    })->name('leagues.create');
    
    Route::get('leagues/{id}', function ($id) {
        return Inertia::render('leagues/show', ['id' => $id]);
    })->name('leagues.show');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
