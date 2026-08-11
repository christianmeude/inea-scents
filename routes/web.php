<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::resource('packages', PackageController::class)->names('admin.packages');
    Route::resource('bookings', BookingController::class)->names('admin.bookings');
    Route::patch('bookings/{booking}/approve', [BookingController::class, 'approve'])->name('admin.bookings.approve');

    Route::get('calendar', [CalendarController::class, 'index'])->name('admin.calendar.index');
    Route::post('calendar/toggle-block', [CalendarController::class, 'toggleBlock'])->name('admin.calendar.toggle-block');
});

require __DIR__.'/auth.php';
