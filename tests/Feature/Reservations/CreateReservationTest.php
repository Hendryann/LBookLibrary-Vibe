<?php

use App\Enums\CopyStatus;
use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an authenticated member to reserve a book with no available copies', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $response = $this->actingAs($user)->postJson('/reservations', [
        'book_id' => $book->id,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.status', ReservationStatus::PENDING->value);
    $response->assertJsonPath('data.queue_position', 1);

    $this->assertDatabaseHas('reservations', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::PENDING->value,
    ]);
});

it('rejects reservation when an available copy exists', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    $response = $this->actingAs($user)->postJson('/reservations', [
        'book_id' => $book->id,
    ]);

    $response->assertStatus(409);
    $this->assertDatabaseCount('reservations', 0);
});

it('rejects duplicate active reservations for the same user and book', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::PENDING,
        'queue_position' => 1,
    ]);

    $response = $this->actingAs($user)->postJson('/reservations', [
        'book_id' => $book->id,
    ]);

    $response->assertStatus(409);
    $this->assertDatabaseCount('reservations', 1);
});

it('rejects reservation for a non-existent book with a validation error', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($user)->postJson('/reservations', [
        'book_id' => 999999,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('book_id');
});

it('rejects unauthenticated reservation attempts', function () {
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $response = $this->postJson('/reservations', [
        'book_id' => $book->id,
    ]);

    $response->assertStatus(401);
});

it('assigns sequential queue positions to multiple reservations on the same book', function () {
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $userOne = User::factory()->create(['role' => Role::MEMBER]);
    $userTwo = User::factory()->create(['role' => Role::MEMBER]);

    $this->actingAs($userOne)->postJson('/reservations', ['book_id' => $book->id])
        ->assertJsonPath('data.queue_position', 1);

    $this->actingAs($userTwo)->postJson('/reservations', ['book_id' => $book->id])
        ->assertJsonPath('data.queue_position', 2);
});
