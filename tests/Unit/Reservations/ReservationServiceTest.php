<?php

use App\Enums\CopyStatus;
use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Exceptions\ReservationException;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Reservation;
use App\Models\User;
use App\Repositories\ReservationRepository;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new ReservationService(new ReservationRepository());
});

it('throws when reserving a non-existent book', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);

    expect(fn () => $this->service->create($user, 999999))
        ->toThrow(ReservationException::class);
});

it('throws when an available copy exists for the book', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    expect(fn () => $this->service->create($user, $book->id))
        ->toThrow(ReservationException::class);
});

it('throws on a duplicate active reservation by the same user', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $this->service->create($user, $book->id);

    expect(fn () => $this->service->create($user, $book->id))
        ->toThrow(ReservationException::class);
});

it('calculates sequential queue positions correctly across several reservations', function () {
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $userOne = User::factory()->create(['role' => Role::MEMBER]);
    $userTwo = User::factory()->create(['role' => Role::MEMBER]);
    $userThree = User::factory()->create(['role' => Role::MEMBER]);

    $reservationOne = $this->service->create($userOne, $book->id);
    $reservationTwo = $this->service->create($userTwo, $book->id);
    $reservationThree = $this->service->create($userThree, $book->id);

    expect($reservationOne->queue_position)->toBe(1)
        ->and($reservationTwo->queue_position)->toBe(2)
        ->and($reservationThree->queue_position)->toBe(3);
});

it('fulfills the earliest pending reservation and reindexes the remaining queue', function () {
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $userOne = User::factory()->create(['role' => Role::MEMBER]);
    $userTwo = User::factory()->create(['role' => Role::MEMBER]);

    $first = $this->service->create($userOne, $book->id);
    $second = $this->service->create($userTwo, $book->id);

    $fulfilled = $this->service->fulfillNextForBook($book->id);

    expect($fulfilled->id)->toBe($first->id)
        ->and($fulfilled->status)->toBe(ReservationStatus::FULFILLED)
        ->and($second->fresh()->queue_position)->toBe(1);
});

it('returns null when fulfilling a book with no pending reservations', function () {
    $book = Book::factory()->create();

    $result = $this->service->fulfillNextForBook($book->id);

    expect($result)->toBeNull();
});

it('does not refulfill a reservation that already left the active queue', function () {
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $user = User::factory()->create(['role' => Role::MEMBER]);
    $this->service->create($user, $book->id);

    $this->service->fulfillNextForBook($book->id);
    $second = $this->service->fulfillNextForBook($book->id);

    expect($second)->toBeNull();
});

it('cancels a pending reservation and reindexes the remaining queue', function () {
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $userOne = User::factory()->create(['role' => Role::MEMBER]);
    $userTwo = User::factory()->create(['role' => Role::MEMBER]);

    $first = $this->service->create($userOne, $book->id);
    $second = $this->service->create($userTwo, $book->id);

    $cancelled = $this->service->cancel($first->id, $userOne);

    expect($cancelled->status)->toBe(ReservationStatus::CANCELLED)
        ->and($second->fresh()->queue_position)->toBe(1);
});

it('throws when cancelling an already cancelled reservation', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::CANCELLED,
        'queue_position' => 1,
    ]);

    expect(fn () => $this->service->cancel($reservation->id, $user))
        ->toThrow(ReservationException::class);
});

it('throws when cancelling an already fulfilled reservation', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::FULFILLED,
        'queue_position' => 1,
    ]);

    expect(fn () => $this->service->cancel($reservation->id, $user))
        ->toThrow(ReservationException::class);
});

it('throws when a non-owner, non-staff user tries to cancel a reservation', function () {
    $book = Book::factory()->create();
    $owner = User::factory()->create(['role' => Role::MEMBER]);
    $intruder = User::factory()->create(['role' => Role::MEMBER]);
    $reservation = Reservation::factory()->create([
        'user_id' => $owner->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::PENDING,
        'queue_position' => 1,
    ]);

    expect(fn () => $this->service->cancel($reservation->id, $intruder))
        ->toThrow(ReservationException::class);
});

it('allows staff to view all reservations while members only see their own', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);
    $member = User::factory()->create(['role' => Role::MEMBER]);
    $otherMember = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();

    Reservation::factory()->create(['user_id' => $member->id, 'book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 1]);
    Reservation::factory()->create(['user_id' => $otherMember->id, 'book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 2]);

    expect($this->service->listForViewer($admin)->total())->toBe(2)
        ->and($this->service->listForViewer($member)->total())->toBe(1);
});
