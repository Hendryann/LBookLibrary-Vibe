<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReservationController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;

Route::get('/', fn() => redirect()->route('books.index'));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login.submit');
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Dashboard & Password
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::put('/password', [AuthController::class, 'updatePassword'])->name('auth.password.update');
});

// Books
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::middleware('auth')->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
});
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// Authors
Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
Route::middleware('auth')->group(function () {
    Route::get('/authors/create', [AuthorController::class, 'create'])->name('authors.create');
    Route::post('/authors', [AuthorController::class, 'store'])->name('authors.store');
    Route::get('/authors/{author}/edit', [AuthorController::class, 'edit'])->name('authors.edit');
    Route::put('/authors/{author}', [AuthorController::class, 'update'])->name('authors.update');
    Route::delete('/authors/{author}', [AuthorController::class, 'destroy'])->name('authors.destroy');
});
Route::get('/authors/{author}', [AuthorController::class, 'show'])->name('authors.show');
Route::get('/authors/{author}/books', [AuthorController::class, 'books'])->name('authors.books');

// Categories
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::middleware('auth')->group(function () {
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/categories/{category}/books', [CategoryController::class, 'books'])->name('categories.books');

// === Domain 3: Inventory & Physical Copies ===

// Book detail & copies (accessible to all authenticated users)
Route::middleware(['auth'])->group(function () {

    Route::get('/books/{book}/copies', [App\Http\Controllers\BookCopyController::class, 'index'])
        ->name('books.copies.index');

    Route::get('/books/{book}/availability', [App\Http\Controllers\BookCopyController::class, 'availability'])
        ->name('books.availability');

    // Admin & Librarian only (enforced inside BookCopyController::middleware())
    Route::post('/books/{book}/copies', [App\Http\Controllers\BookCopyController::class, 'store'])
        ->name('books.copies.store');

    Route::put('/books/{book}/copies/{copyId}', [App\Http\Controllers\BookCopyController::class, 'update'])
        ->name('books.copies.update');

    Route::delete('/books/{book}/copies/{copyId}', [App\Http\Controllers\BookCopyController::class, 'destroy'])
        ->name('books.copies.destroy');
});

// ===Domain 4 : Borrowing Lifecycle===
Route::middleware('auth')->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/overdue', [TransactionController::class, 'overdue'])
        ->name('transactions.overdue');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])
        ->whereNumber('id')
        ->name('transactions.show');
    Route::post('/transactions/borrow', [TransactionController::class, 'borrow'])
        ->name('transactions.borrow');
    Route::patch('/transactions/{id}/return', [TransactionController::class, 'returnBook'])
        ->whereNumber('id')
        ->name('transactions.return');
    Route::patch('/transactions/{id}/extend', [TransactionController::class, 'extend'])
        ->whereNumber('id')
        ->name('transactions.extend');
});
// ===Domain 5 : Reservation System===
Route::middleware(['auth'])->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index'])
        ->name('reservations.index');

    Route::post('/reservations', [ReservationController::class, 'store'])
        ->name('reservations.store');

    Route::get('/reservations/{id}', [ReservationController::class, 'show'])
        ->whereNumber('id')
        ->name('reservations.show');

    Route::patch('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])
        ->whereNumber('id')
        ->name('reservations.cancel');

    Route::get('/books/{id}/reservations', [ReservationController::class, 'bookReservations'])
        ->whereNumber('id')
        ->name('books.reservations');
});

Route::middleware('auth.session')->group(function () {
    // User Profile
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/history', [UserController::class, 'history'])->name('users.history');
    Route::get('/users/recommendations', [UserController::class, 'recommendations'])->name('users.recommendations');
    Route::get('/users/{id}', [UserController::class, 'show'])->whereNumber('id')->name('users.show');
    Route::put('/users/{id}', [UserController::class, 'update'])->whereNumber('id')->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->whereNumber('id')->name('users.destroy');

    // Reviews (nested under books)
    Route::get('/books/{book}/reviews', [ReviewController::class, 'index'])->name('books.reviews.index');
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('books.reviews.store');
    Route::delete('/books/{book}/reviews/{review}', [ReviewController::class, 'destroy'])->name('books.reviews.destroy');
});

