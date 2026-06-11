<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root — redirect to login
Route::get('/', fn () => redirect()->route('login'));

// ── Guest-only routes ────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/auth/register', [AuthController::class, 'showRegister'])
        ->name('auth.register');

    Route::post('/auth/register', [AuthController::class, 'register'])
        ->name('auth.register.submit');

    // Named 'login' so Laravel's Authenticate middleware can resolve it
    Route::get('/auth/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/auth/login', [AuthController::class, 'login'])
        ->name('auth.login.submit');
});

// ── Authenticated routes ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::put('/auth/password', [AuthController::class, 'updatePassword'])
        ->name('auth.password.update');

    Route::get('/dashboard', fn () => view('dashboard'))
        ->name('dashboard');
});