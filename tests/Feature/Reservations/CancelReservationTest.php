<?php

use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a user to cancel their own pending reservation', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::PENDING,
        'queue_position' => 1,
    ]);

    $response = $this->actingAs($user)->patchJson("/reservations/{$reservation->id}/cancel");

    $response->assertOk();
    $response->assertJsonPath('data.status', ReservationStatus::CANCELLED->value);

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => ReservationStatus::CANCELLED->value,
    ]);
});

it('recalculates queue positions of the remaining queue after a cancellation', function () {
    $book = Book::factory()->create();

    $first = Reservation::factory()->create(['book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 1]);
    $second = Reservation::factory()->create(['book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 2]);
    $third = Reservation::factory()->create(['book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 3]);

    $this->actingAs($first->user)->patchJson("/reservations/{$first->id}/cancel")->assertOk();

    expect($second->fresh()->queue_position)->toBe(1)
        ->and($third->fresh()->queue_position)->toBe(2);
});

it('prevents a user from cancelling another users reservation', function () {
    $owner = User::factory()->create(['role' => Role::MEMBER]);
    $intruder = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $reservation = Reservation::factory()->create([
        'user_id' => $owner->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::PENDING,
        'queue_position' => 1,
    ]);

    $response = $this->actingAs($intruder)->patchJson("/reservations/{$reservation->id}/cancel");

    $response->assertStatus(403);

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => ReservationStatus::PENDING->value,
    ]);
});

it('rejects cancelling an already cancelled reservation', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::CANCELLED,
        'queue_position' => 1,
    ]);

    $response = $this->actingAs($user)->patchJson("/reservations/{$reservation->id}/cancel");

    $response->assertStatus(409);
});

it('rejects cancelling an already fulfilled reservation', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::FULFILLED,
        'queue_position' => 1,
    ]);

    $response = $this->actingAs($user)->patchJson("/reservations/{$reservation->id}/cancel");

    $response->assertStatus(409);
});

it('returns 404 when cancelling a non-existent reservation', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($user)->patchJson('/reservations/999999/cancel');

    $response->assertStatus(404);
});

it('allows an admin to cancel any users reservation', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);
    $owner = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $reservation = Reservation::factory()->create([
        'user_id' => $owner->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::PENDING,
        'queue_position' => 1,
    ]);

    $response = $this->actingAs($admin)->patchJson("/reservations/{$reservation->id}/cancel");

    $response->assertOk();
});
