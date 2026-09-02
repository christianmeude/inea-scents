<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\DashboardController;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
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

// Root: health hint locally; redirect to FRONTEND_URL when configured.
// 12-Factor: FRONTEND_URL via config (not direct env() under cache).
Route::get('/', function () {
    $frontend = config('app.frontend_url');

    if (app()->environment('local') && empty($frontend)) {
        return response()->json([
            'app' => config('app.name'),
            'env' => app()->environment(),
            'message' => 'Backend is running. Set FRONTEND_URL to enable redirect, or use /api/ping for health.',
            'health' => url('/api/ping'),
            'admin' => url('/admin/dashboard'),
        ]);
    }

    if (empty($frontend)) {
        abort(404, 'FRONTEND_URL not configured for this environment.');
    }

    $target = trim(explode(',', $frontend)[0]);
    if ($target === '') {
        abort(404, 'FRONTEND_URL is empty.');
    }

    return redirect($target);
});

Route::get('/health', fn () => response()->json(['status' => 'ok']));
