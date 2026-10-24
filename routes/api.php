<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DoctorController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login');
Route::get('/doctors', [DoctorController::class, 'index'])->middleware('throttle:catalog');
Route::get('/doctors/{doctor}/services', [DoctorController::class, 'services'])->middleware('throttle:catalog');
Route::get('/doctors/{doctor}/available-slots', [AvailabilityController::class, 'index'])
    ->middleware('throttle:catalog');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/my-bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/preview', [BookingController::class, 'preview']);
    Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:bookings');
});
