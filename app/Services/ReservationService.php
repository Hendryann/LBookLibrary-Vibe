<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Exceptions\ReservationException;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\User;
use App\Repositories\ReservationRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function __construct(private readonly ReservationRepository $reservations)
    {
    }

    /**
     * Create a PENDING reservation for the given user/book.
     *
     * @throws ReservationException
     */
    public function create(User $user, int $bookId): Reservation
    {
        $book = Book::find($bookId);

        if (! $book) {
            throw ReservationException::bookNotFound();
        }

        if ($this->reservations->countAvailableCopies($bookId) > 0) {
            throw ReservationException::availableCopiesExist();
        }

        if ($this->reservations->findActivePendingByUserAndBook($user->id, $bookId)) {
            throw ReservationException::duplicateReservation();
        }

        return DB::transaction(function () use ($user, $bookId) {
            $position = $this->reservations->nextQueuePositionForBook($bookId);

            return $this->reservations->create([
                'user_id' => $user->id,
                'book_id' => $bookId,
                'queue_position' => $position,
                'reserved_at' => now(),
                'status' => ReservationStatus::PENDING,
            ]);
        });
    }

    /**
     * @throws ReservationException
     */
    public function findForViewer(int $id, User $user): Reservation
    {
        $reservation = $this->reservations->find($id);

        if (! $reservation) {
            throw ReservationException::reservationNotFound();
        }

        if (! $this->canView($reservation, $user)) {
            throw ReservationException::unauthorized();
        }

        return $reservation;
    }

    /**
     * Cancel a reservation owned by the user (or any reservation, for staff).
     *
     * @throws ReservationException
     */
    public function cancel(int $id, User $user): Reservation
    {
        $reservation = $this->reservations->find($id);

        if (! $reservation) {
            throw ReservationException::reservationNotFound();
        }

        if (! $this->canManage($reservation, $user)) {
            throw ReservationException::unauthorized();
        }

        if ($reservation->status === ReservationStatus::CANCELLED) {
            throw ReservationException::alreadyCancelled();
        }

        if ($reservation->status === ReservationStatus::FULFILLED) {
            throw ReservationException::alreadyFulfilled();
        }

        return DB::transaction(function () use ($reservation) {
            $this->reservations->update($reservation, [
                'status' => ReservationStatus::CANCELLED,
            ]);

            $this->reservations->reindexQueue($reservation->book_id);

            return $reservation->fresh();
        });
    }

    /**
     * Fulfill the earliest eligible (lowest queue position) PENDING reservation
     * for a book. Intended to be called once a BookCopy for that book transitions
     * to AVAILABLE (e.g. from the existing Domain 4 return-book flow). Returns
     * null when no PENDING reservation exists for the book.
     */
    public function fulfillNextForBook(int $bookId): ?Reservation
    {
        return DB::transaction(function () use ($bookId) {
            $next = $this->reservations->nextEligibleForBook($bookId);

            if (! $next) {
                return null;
            }

            $this->reservations->update($next, [
                'status' => ReservationStatus::FULFILLED,
            ]);

            $this->reservations->reindexQueue($bookId);

            return $next->fresh();
        });
    }

    /**
     * Staff (ADMIN/LIBRARIAN) see every reservation; members see only their own.
     */
    public function listForViewer(User $user): LengthAwarePaginator
    {
        if ($this->isStaff($user)) {
            return $this->reservations->paginateAll();
        }

        return $this->reservations->paginateForUser($user->id);
    }

    /**
     * @throws ReservationException
     */
    public function listForBook(int $bookId, User $user): Collection
    {
        $book = Book::find($bookId);

        if (! $book) {
            throw ReservationException::bookNotFound();
        }

        if ($this->isStaff($user)) {
            return $this->reservations->allForBook($bookId);
        }

        return $this->reservations->activeForBook($bookId)
            ->map(function (Reservation $reservation) use ($user) {
                if ($reservation->user_id !== $user->id) {
                    $reservation->makeHidden('user');
                }

                return $reservation;
            });
    }

    private function canView(Reservation $reservation, User $user): bool
    {
        return $reservation->user_id === $user->id || $this->isStaff($user);
    }

    private function canManage(Reservation $reservation, User $user): bool
    {
        return $reservation->user_id === $user->id || $this->isStaff($user);
    }

    private function isStaff(User $user): bool
    {
        return in_array($user->role, [Role::ADMIN, Role::LIBRARIAN], true);
    }
}
