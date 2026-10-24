<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login');
Route::get('/doctors/{doctor}/available-slots', [AvailabilityController::class, 'index'])
    ->middleware('throttle:catalog');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/bookings/preview', [BookingController::class, 'preview']);
    Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:bookings');
});
