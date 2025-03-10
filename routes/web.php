<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalculationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::post('/calculate', [CalculationController::class, 'store'])->name('calculate.store');
    Route::get('/history', [CalculationController::class, 'history'])->name('calculate.history');
    Route::get('/export-history', [CalculationController::class, 'exportHistory'])->name('calculate.export');
    Route::get('/export-csv', [CalculationController::class, 'exportCsv'])->name('calculate.exportCsv');
    Route::post('/calculate-preview', [CalculationController::class, 'preview']);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
