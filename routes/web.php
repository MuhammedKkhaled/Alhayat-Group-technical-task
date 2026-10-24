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

Route::prefix('doctors')->name('doctors.')->middleware('throttle:catalog')->group(function () {
    Route::get('/', [DoctorController::class, 'index'])->name('index');
    Route::get('/{doctor}', [DoctorController::class, 'show'])->name('show');
    Route::get('/{doctor}/availability', [BookingController::class, 'availability'])->name('availability');
});

Route::middleware('auth')->group(function () {
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.index');

    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/create', [BookingController::class, 'create'])->name('create');
        Route::post('/preview', [BookingController::class, 'preview'])->name('preview');
        Route::post('/', [BookingController::class, 'store'])
            ->middleware('throttle:bookings')
            ->name('store');
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
    });
});
