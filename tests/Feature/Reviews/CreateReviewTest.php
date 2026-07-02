<?php

use App\Enums\Role;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows authenticated user to create a review', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('books.reviews.store', $book->id), [
            'rating' => 5,
            'comment' => 'Excellent book.',
        ]);

    $response->assertRedirect(route('books.show', $book->id));
    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'rating' => 5,
        'comment' => 'Excellent book.',
    ]);
});

it('rejects review for a non-existent book', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($user)
        ->post(route('books.reviews.store', 999999), [
            'rating' => 4,
        ]);

    $response->assertStatus(404);
});

it('rejects duplicate review for the same user and book', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();

    Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'rating' => 4,
    ]);

    $response = $this->actingAs($user)
        ->post(route('books.reviews.store', $book->id), [
            'rating' => 3,
            'comment' => 'Trying again.',
        ]);

    $response->assertStatus(409);
    $this->assertDatabaseCount('reviews', 1);
});

it('rejects invalid rating values', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('books.reviews.store', $book->id), [
            'rating' => 6,
        ]);

    $response->assertSessionHasErrors('rating');
    $this->assertDatabaseCount('reviews', 0);
});

it('rejects review creation without authentication', function () {
    $book = Book::factory()->create();

    $response = $this->post(route('books.reviews.store', $book->id), [
        'rating' => 4,
    ]);

    $response->assertRedirect(route('login'));
});