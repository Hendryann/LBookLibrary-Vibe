<?php

/*
|--------------------------------------------------------------------------
| Reservation System routes
|--------------------------------------------------------------------------
| Add this block inside the existing authenticated route group in
| routes/web.php (the same group that already contains the Domain 2-4
| routes). Do not replace the whole file — merge this block in and add
| the `use` import below to the top of routes/web.php alongside the
| other controller imports.
*/

use App\Http\Controllers\ReservationController;

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
