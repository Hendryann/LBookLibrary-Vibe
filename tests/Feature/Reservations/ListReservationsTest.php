<?php

use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only the authenticated members own reservations', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $otherUser = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();

    Reservation::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 1]);
    Reservation::factory()->create(['user_id' => $otherUser->id, 'book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 2]);

    $response = $this->actingAs($user)->getJson('/reservations');

    $response->assertOk();
    $response->assertJsonCount(1, 'data.data');
});

it('lists all reservations for admins', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);
    $userOne = User::factory()->create(['role' => Role::MEMBER]);
    $userTwo = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();

    Reservation::factory()->create(['user_id' => $userOne->id, 'book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 1]);
    Reservation::factory()->create(['user_id' => $userTwo->id, 'book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 2]);

    $response = $this->actingAs($admin)->getJson('/reservations');

    $response->assertOk();
    $response->assertJsonCount(2, 'data.data');
});

it('rejects unauthenticated access to the reservation list', function () {
    $response = $this->getJson('/reservations');

    $response->assertStatus(401);
});

it('shows reservation details to its owner', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $reservation = Reservation::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 1]);

    $response = $this->actingAs($user)->getJson("/reservations/{$reservation->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $reservation->id);
});

it('prevents a user from viewing another users reservation', function () {
    $owner = User::factory()->create(['role' => Role::MEMBER]);
    $intruder = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $reservation = Reservation::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 1]);

    $response = $this->actingAs($intruder)->getJson("/reservations/{$reservation->id}");

    $response->assertStatus(403);
});

it('returns 404 when viewing a non-existent reservation', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($user)->getJson('/reservations/999999');

    $response->assertStatus(404);
});
