<?php

use App\Http\Controllers\DeployController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PublicPropertyController;
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

Route::get('/properties', [PublicPropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{slug}', [PublicPropertyController::class, 'show'])->name('properties.show');

// ═══════════════════════════════════════════════════
// DEPLOY Endpoints — token-protected, no CSRF
// ═══════════════════════════════════════════════════
Route::post('/deploy/upload', [DeployController::class, 'upload']);
Route::post('/deploy/update-env', [DeployController::class, 'updateEnv']);
Route::get('/deploy/cache-clear', [DeployController::class, 'cacheClear']);
Route::match(['get', 'post'], '/deploy/check', [DeployController::class, 'check']);

// Feature routes (Bookings, Properties) are loaded by their
// respective ServiceProviders via Route::group in boot().
