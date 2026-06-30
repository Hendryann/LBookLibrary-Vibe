<?php

namespace App\Repositories;

use App\Enums\CopyStatus;
use App\Enums\ReservationStatus;
use App\Models\BookCopy;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReservationRepository
{
    public function find(int $id): ?Reservation
    {
        return Reservation::with(['user', 'book'])->find($id);
    }

    public function create(array $attributes): Reservation
    {
        return Reservation::create($attributes);
    }

    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Reservation::with('book')
            ->where('user_id', $userId)
            ->orderByDesc('reserved_at')
            ->paginate($perPage);
    }

    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        return Reservation::with(['user', 'book'])
            ->orderByDesc('reserved_at')
            ->paginate($perPage);
    }

    public function activeForBook(int $bookId): Collection
    {
        return Reservation::with('user')
            ->where('book_id', $bookId)
            ->where('status', ReservationStatus::PENDING)
            ->orderBy('queue_position')
            ->get();
    }

    public function allForBook(int $bookId): Collection
    {
        return Reservation::with('user')
            ->where('book_id', $bookId)
            ->orderBy('queue_position')
            ->get();
    }

    public function findActivePendingByUserAndBook(int $userId, int $bookId): ?Reservation
    {
        return Reservation::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->where('status', ReservationStatus::PENDING)
            ->first();
    }

    public function nextQueuePositionForBook(int $bookId): int
    {
        $max = Reservation::where('book_id', $bookId)
            ->where('status', ReservationStatus::PENDING)
            ->max('queue_position');

        return ((int) $max) + 1;
    }

    public function countAvailableCopies(int $bookId): int
    {
        return BookCopy::where('book_id', $bookId)
            ->where('status', CopyStatus::AVAILABLE)
            ->count();
    }

    public function nextEligibleForBook(int $bookId): ?Reservation
    {
        return Reservation::where('book_id', $bookId)
            ->where('status', ReservationStatus::PENDING)
            ->orderBy('queue_position')
            ->first();
    }

    public function update(Reservation $reservation, array $attributes): Reservation
    {
        $reservation->fill($attributes);
        $reservation->save();

        return $reservation->fresh();
    }

    /**
     * Re-sequence queue_position (1..n) for all PENDING reservations of a book,
     * ordered by their current queue_position. Called after a cancellation or
     * fulfillment to keep the active queue contiguous and ordered.
     */
    public function reindexQueue(int $bookId): void
    {
        $reservations = Reservation::where('book_id', $bookId)
            ->where('status', ReservationStatus::PENDING)
            ->orderBy('queue_position')
            ->get();

        $position = 1;

        foreach ($reservations as $reservation) {
            if ($reservation->queue_position !== $position) {
                $reservation->queue_position = $position;
                $reservation->save();
            }

            $position++;
        }
    }
}
