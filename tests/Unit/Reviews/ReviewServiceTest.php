<?php

use App\Enums\Role;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Repositories\BookRepository;
use App\Repositories\ReviewRepository;
use App\Services\ReviewService;
use Illuminate\Validation\ValidationException;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->service = new ReviewService(new ReviewRepository(), new BookRepository());
});

it('creates a review when no duplicate exists', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $review = $this->service->createReview($book->id, $user, ['rating' => 5, 'comment' => 'Great']);

    expect($review->rating)->toBe(5);
});

it('throws an exception for duplicate review', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

    expect(fn () => $this->service->createReview($book->id, $user, ['rating' => 3]))
        ->toThrow(ValidationException::class);
});

it('throws an exception for a non-existent book', function () {
    $user = User::factory()->create();

    expect(fn () => $this->service->createReview(999999, $user, ['rating' => 4]))
        ->toThrow(ValidationException::class);
});

it('allows the review owner to delete their review', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

    $result = $this->service->deleteReview($book->id, $review->id, $user);

    expect($result)->toBeTrue();
});

it('prevents a non-owner non-admin from deleting a review', function () {
    $owner = User::factory()->create(['role' => Role::MEMBER]);
    $other = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

    expect(fn () => $this->service->deleteReview($book->id, $review->id, $other))
        ->toThrow(ValidationException::class);
});

it('allows an admin to delete any review', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);
    $owner = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id]);

    $result = $this->service->deleteReview($book->id, $review->id, $admin);

    expect($result)->toBeTrue();
});