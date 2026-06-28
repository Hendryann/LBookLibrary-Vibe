<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root — redirect to login
Route::get('/', fn() => redirect()->route('login'));

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

    Route::get('/dashboard', fn() => view('dashboard'))
        ->name('dashboard');
});

// Books
Route::get('/books',                [BookController::class, 'index'])->name('books.index');
Route::get('/books/{id}',           [BookController::class, 'show'])->name('books.show');
Route::middleware('auth')->group(function () {
    Route::get('/books/create',     [BookController::class, 'create'])->name('books.create');
    Route::post('/books',           [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{id}/edit',  [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{id}',       [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{id}',    [BookController::class, 'destroy'])->name('books.destroy');
});

// Authors
Route::get('/authors',                       [AuthorController::class, 'index'])->name('authors.index');
Route::get('/authors/{id}',                  [AuthorController::class, 'show'])->name('authors.show');
Route::get('/authors/{id}/books',            [AuthorController::class, 'books'])->name('authors.books');
Route::middleware('auth')->group(function () {
    Route::get('/authors/create',            [AuthorController::class, 'create'])->name('authors.create');
    Route::post('/authors',                  [AuthorController::class, 'store'])->name('authors.store');
    Route::get('/authors/{id}/edit',         [AuthorController::class, 'edit'])->name('authors.edit');
    Route::put('/authors/{id}',              [AuthorController::class, 'update'])->name('authors.update');
    Route::delete('/authors/{id}',           [AuthorController::class, 'destroy'])->name('authors.destroy');
});

// Categories
Route::get('/categories',                    [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{id}',               [CategoryController::class, 'show'])->name('categories.show');
Route::get('/categories/{id}/books',         [CategoryController::class, 'books'])->name('categories.books');
Route::middleware('auth')->group(function () {
    Route::get('/categories/create',         [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories',               [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit',      [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}',           [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}',        [CategoryController::class, 'destroy'])->name('categories.destroy');
});
