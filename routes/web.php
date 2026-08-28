<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\MagicLoginController;

// The magic login route must be outside the 'auth' middleware group but still inside web.
Route::get('/admin/magic-login', [MagicLoginController::class, 'login'])
    ->middleware('signed')
    ->name('admin.magic.login');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('packages', PackageController::class);
    Route::resource('bookings', BookingController::class);
    Route::patch('bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('calendar/toggle-block', [CalendarController::class, 'toggleBlock'])->name('calendar.toggle-block');
});

require __DIR__.'/auth.php';

// Redirect the base URL to the Vercel Frontend
Route::get('/', function () {
    return redirect(env('FRONTEND_URL', 'https://inea-scents-client.vercel.app'));
});
