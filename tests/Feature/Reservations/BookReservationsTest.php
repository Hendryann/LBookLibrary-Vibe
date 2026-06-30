<?php

use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists the reservation queue for a book ordered by queue position', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);
    $book = Book::factory()->create();

    Reservation::factory()->create(['book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 1]);
    Reservation::factory()->create(['book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 2]);
    Reservation::factory()->create(['book_id' => $book->id, 'status' => ReservationStatus::PENDING, 'queue_position' => 3]);

    $response = $this->actingAs($admin)->getJson("/books/{$book->id}/reservations");

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
    $response->assertJsonPath('data.0.queue_position', 1);
    $response->assertJsonPath('data.2.queue_position', 3);
});

it('hides other members identities from a non-staff viewer of the book queue', function () {
    $viewer = User::factory()->create(['role' => Role::MEMBER]);
    $otherUser = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();

    Reservation::factory()->create([
        'user_id' => $otherUser->id,
        'book_id' => $book->id,
        'status' => ReservationStatus::PENDING,
        'queue_position' => 1,
    ]);

    $response = $this->actingAs($viewer)->getJson("/books/{$book->id}/reservations");

    $response->assertOk();
    expect($response->json('data.0.user'))->toBeNull();
});

it('returns 404 when requesting reservations for a non-existent book', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);

    $response = $this->actingAs($admin)->getJson('/books/999999/reservations');

    $response->assertStatus(404);
});
