<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DoctorController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login');
Route::prefix('doctors')->middleware('throttle:catalog')->group(function () {
    Route::get('/', [DoctorController::class, 'index']);
    Route::get('/{doctor}/services', [DoctorController::class, 'services']);
    Route::get('/{doctor}/available-slots', [AvailabilityController::class, 'index']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/my-bookings', [BookingController::class, 'index']);

    Route::prefix('bookings')->group(function () {
        Route::get('/{booking}', [BookingController::class, 'show']);
        Route::post('/preview', [BookingController::class, 'preview']);
        Route::post('/', [BookingController::class, 'store'])->middleware('throttle:bookings');
    });
});
