<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\EventController; 

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'verified'])->group(function () {
/*
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('events', EventController::class);
    });
*/
    // Staff routes (admin + staff)
    Route::middleware(['role:admin,staff'])->prefix('staff')->name('staff.')->group(function () {
        // check-in, leads — added in Phase 2
    });

    // Sponsor routes
    Route::middleware(['role:sponsor'])->prefix('sponsor')->name('sponsor.')->group(function () {
        // Phase 6
    });

    // Speaker routes
    Route::middleware(['role:speaker'])->prefix('speaker')->name('speaker.')->group(function () {
        // Phase 4
    });
});

require __DIR__.'/auth.php';
