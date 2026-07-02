<?php

use App\Enums\Role;
use App\Enums\TransactionStatus;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('returns recommendations based on borrowing history categories', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $category = Category::factory()->create();

    $borrowedBook = Book::factory()->create();
    $borrowedBook->categories()->attach($category->id);
    $copy = BookCopy::factory()->create(['book_id' => $borrowedBook->id]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'status' => TransactionStatus::RETURNED,
        'borrow_date' => now()->subDays(5),
        'due_date' => now()->subDays(1),
        'return_date' => now(),
        'fine_amount' => 0,
    ]);

    $recommendedBook = Book::factory()->create();
    $recommendedBook->categories()->attach($category->id);

    $response = $this->actingAs($user)->get(route('users.recommendations'));

    $response->assertOk();
    $response->assertViewHas('recommendations', function ($recommendations) use ($recommendedBook) {
        return $recommendations->pluck('id')->contains($recommendedBook->id);
    });
});

it('excludes already borrowed books from recommendations', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $category = Category::factory()->create();

    $borrowedBook = Book::factory()->create();
    $borrowedBook->categories()->attach($category->id);
    $copy = BookCopy::factory()->create(['book_id' => $borrowedBook->id]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'status' => TransactionStatus::RETURNED,
        'borrow_date' => now()->subDays(5),
        'due_date' => now()->subDays(1),
        'return_date' => now(),
        'fine_amount' => 0,
    ]);

    $response = $this->actingAs($user)->get(route('users.recommendations'));

    $response->assertOk();
    $response->assertViewHas('recommendations', function ($recommendations) use ($borrowedBook) {
        return ! $recommendations->pluck('id')->contains($borrowedBook->id);
    });
});

it('does not modify any data when generating recommendations', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);

    $countBefore = \App\Models\Transaction::count();

    $this->actingAs($user)->get(route('users.recommendations'));

    $countAfter = \App\Models\Transaction::count();

    expect($countAfter)->toBe($countBefore);
});

it('requires authentication to view recommendations', function () {
    $response = $this->get(route('users.recommendations'));

    $response->assertRedirect(route('login'));
});