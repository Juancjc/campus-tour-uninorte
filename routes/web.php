<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');
Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('health');
Route::get('/professions', [ProfessionController::class, 'index'])->middleware('throttle:profession-search')->name('professions.index');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/rankings', RankingController::class)->name('rankings.index');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::middleware('throttle:game-write')->group(function () {
        Route::post('/games/{game}/sessions', [GameSessionController::class, 'store'])->name('game-sessions.store');
        Route::post('/games/{game}/sessions/{gameSession}/answer', [GameSessionController::class, 'answer'])->name('game-sessions.answer');
        Route::post('/games/{game}/sessions/{gameSession}/events', [GameSessionController::class, 'events'])->name('game-sessions.events');
        Route::post('/games/{game}/sessions/{gameSession}/complete', [GameSessionController::class, 'complete'])->name('game-sessions.complete');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{type}.csv', [ExportController::class, 'csv'])->name('reports.csv');
    Route::get('/reports/{type}.xlsx', [ExportController::class, 'xlsx'])->name('reports.xlsx');
});

require __DIR__.'/auth.php';
