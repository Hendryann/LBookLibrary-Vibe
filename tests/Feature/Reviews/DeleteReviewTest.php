<?php

use App\Enums\Role;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows a user to delete their own review', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

    $response = $this->actingAs($user)
        ->delete(route('books.reviews.destroy', [$book->id, $review->id]));

    $response->assertRedirect(route('books.show', $book->id));
    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

it('rejects deletion of another users review by a regular member', function () {
    $owner = User::factory()->create(['role' => Role::MEMBER]);
    $other = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

    $response = $this->actingAs($other)
        ->delete(route('books.reviews.destroy', [$book->id, $review->id]));

    $response->assertStatus(403);
    $this->assertDatabaseHas('reviews', ['id' => $review->id]);
});

it('allows admin to delete any review', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);
    $owner = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

    $response = $this->actingAs($admin)
        ->delete(route('books.reviews.destroy', [$book->id, $review->id]));

    $response->assertRedirect(route('books.show', $book->id));
    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

it('returns 404 when deleting a non-existent review', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();

    $response = $this->actingAs($user)
        ->delete(route('books.reviews.destroy', [$book->id, 999999]));

    $response->assertStatus(404);
});