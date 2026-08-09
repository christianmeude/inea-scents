<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Admin Routes
    Route::resource('packages', \App\Http\Controllers\Admin\PackageController::class)->names('admin.packages');
    Route::resource('bookings', \App\Http\Controllers\Admin\BookingController::class)->names('admin.bookings');
    
    Route::get('calendar', [\App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('admin.calendar.index');
    Route::post('calendar/toggle-block', [\App\Http\Controllers\Admin\CalendarController::class, 'toggleBlock'])->name('admin.calendar.toggle-block');
});

require __DIR__.'/auth.php';
