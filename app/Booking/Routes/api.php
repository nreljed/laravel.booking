<?php

use App\Booking\Controllers\BookingAvailabilityController;
use App\Booking\Controllers\BookingGuestController;
use Illuminate\Support\Facades\Route;

Route::post('/booking/check-availability', [BookingAvailabilityController::class, 'checkAvailabilityApi'])->name('api.booking.check-availability');
Route::get('/booking/cache-version/{propertyId}', [BookingAvailabilityController::class, 'cacheVersion'])->name('api.booking.cache-version');
Route::post('/booking/modify/{bookingId}', [BookingGuestController::class, 'requestModification'])->name('api.booking.modify');
Route::post('/booking/cancel/{bookingId}', [BookingGuestController::class, 'requestCancellation'])->name('api.booking.cancel');
