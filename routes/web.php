<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\DoctorController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/doctors');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/doctors', [DoctorController::class, 'index'])
    ->middleware('throttle:catalog')
    ->name('doctors.index');
Route::get('/doctors/{doctor}', [DoctorController::class, 'show'])
    ->middleware('throttle:catalog')
    ->name('doctors.show');
Route::get('/doctors/{doctor}/availability', [BookingController::class, 'availability'])
    ->middleware('throttle:catalog')
    ->name('doctors.availability');

Route::middleware('auth')->group(function () {
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings/preview', [BookingController::class, 'preview'])->name('bookings.preview');
    Route::post('/bookings', [BookingController::class, 'store'])
        ->middleware('throttle:bookings')
        ->name('bookings.store');
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
});
