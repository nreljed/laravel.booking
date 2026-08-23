<?php

use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ═══════════════════════════════════════════════════
// FRONTEND (Public) Pages — no auth required
// ═══════════════════════════════════════════════════
Route::get('/', function () {
    return Inertia::render('public/welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/services', [PublicController::class, 'services'])->name('services');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');

// Public /properties routes are loaded by nreljed/booking-module (PropertiesServiceProvider)

// ═══════════════════════════════════════════════════
// DEPLOY Endpoints — provided by dashboard-core routes
// (/deploy/upload, /deploy/check, /deploy/cache-clear,
//  /deploy/env, /deploy/create-admin, /deploy/migrate, /deploy/reset)

// Feature routes (Bookings, Properties) are loaded by their
// respective ServiceProviders via Route::group in boot().
