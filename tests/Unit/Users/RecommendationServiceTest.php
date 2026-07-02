<?php

use App\Enums\TransactionStatus;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\RecommendationRepository;
use App\Services\RecommendationService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->service = new RecommendationService(new RecommendationRepository());
});

it('scores books higher when they match more frequently borrowed categories', function () {
    $user = User::factory()->create();
    $popularCategory = Category::factory()->create();
    $rareCategory = Category::factory()->create();

    // Borrow 2 books in popularCategory, 1 in rareCategory
    foreach (range(1, 2) as $i) {
        $borrowed = Book::factory()->create();
        $borrowed->categories()->attach($popularCategory->id);
        $copy = BookCopy::factory()->create(['book_id' => $borrowed->id]);
        Transaction::factory()->create([
            'user_id' => $user->id, 'copy_id' => $copy->id,
            'status' => TransactionStatus::RETURNED, 'fine_amount' => 0,
            'borrow_date' => now()->subDays(10), 'due_date' => now()->subDays(3), 'return_date' => now()->subDays(2),
        ]);
    }

    $borrowedRare = Book::factory()->create();
    $borrowedRare->categories()->attach($rareCategory->id);
    $copyRare = BookCopy::factory()->create(['book_id' => $borrowedRare->id]);
    Transaction::factory()->create([
        'user_id' => $user->id, 'copy_id' => $copyRare->id,
        'status' => TransactionStatus::RETURNED, 'fine_amount' => 0,
        'borrow_date' => now()->subDays(10), 'due_date' => now()->subDays(3), 'return_date' => now()->subDays(2),
    ]);

    $candidatePopular = Book::factory()->create();
    $candidatePopular->categories()->attach($popularCategory->id);

    $candidateRare = Book::factory()->create();
    $candidateRare->categories()->attach($rareCategory->id);

    $recommendations = $this->service->recommendForUser($user->id);

    $popularScore = $recommendations->firstWhere('id', $candidatePopular->id)?->recommendation_score;
    $rareScore = $recommendations->firstWhere('id', $candidateRare->id)?->recommendation_score;

    expect($popularScore)->toBeGreaterThan($rareScore);
});

it('falls back to popular books when user has no borrowing history', function () {
    $user = User::factory()->create();
    Book::factory()->count(3)->create();

    $recommendations = $this->service->recommendForUser($user->id);

    expect($recommendations)->not->toBeEmpty();
});

it('excludes books the user has reserved', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $borrowedBook = Book::factory()->create();
    $borrowedBook->categories()->attach($category->id);
    $copy = BookCopy::factory()->create(['book_id' => $borrowedBook->id]);
    Transaction::factory()->create([
        'user_id' => $user->id, 'copy_id' => $copy->id,
        'status' => TransactionStatus::RETURNED, 'fine_amount' => 0,
        'borrow_date' => now()->subDays(10), 'due_date' => now()->subDays(3), 'return_date' => now()->subDays(2),
    ]);

    $reservedBook = Book::factory()->create();
    $reservedBook->categories()->attach($category->id);
    \App\Models\Reservation::factory()->create([
        'user_id' => $user->id,
        'book_id' => $reservedBook->id,
        'status' => \App\Enums\ReservationStatus::PENDING,
    ]);

    $recommendations = $this->service->recommendForUser($user->id);

    expect($recommendations->pluck('id')->contains($reservedBook->id))->toBeFalse();
});