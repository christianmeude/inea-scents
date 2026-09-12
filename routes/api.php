<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PackageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/availability', [AvailabilityController::class, 'index']);

Route::get('/packages', [PackageController::class, 'index']);
Route::get('/packages/{package}', [PackageController::class, 'show']);

Route::post('/inquiries', [\App\Http\Controllers\Api\InquiryController::class, 'store'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
});

// Ping endpoint for health checks (decoupled CI test)
Route::get('/ping', fn() => 'pong');

Route::post('/webhooks/paymongo', [\App\Http\Controllers\Api\PayMongoWebhookController::class, 'handle'])->middleware('throttle:60,1');

Route::post('/bookings/expire', function (Request $request) {
    if ($request->header('X-Cron-Token') !== env('CRON_TOKEN')) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $expired = \App\Models\Booking::expireStalePending();

    return response()->json(['message' => "Expired {$expired} bookings."]);
});
