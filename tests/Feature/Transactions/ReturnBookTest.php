<?php

use App\Enums\CopyStatus;
use App\Enums\TransactionStatus;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('allows the borrower to return their book', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'borrow_date' => Carbon::now()->subDays(3),
        'due_date' => Carbon::now()->addDays(11),
        'return_date' => null,
        'fine_amount' => 0,
        'status' => TransactionStatus::ACTIVE,
    ]);

    $response = $this->actingAs($user)->patch("/transactions/{$transaction->id}/return");

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'status' => TransactionStatus::RETURNED->value,
    ]);

    $this->assertDatabaseHas('book_copies', [
        'id' => $copy->id,
        'status' => CopyStatus::AVAILABLE->value,
    ]);
});

it('calculates a fine when returning overdue', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'borrow_date' => Carbon::now()->subDays(20),
        'due_date' => Carbon::now()->subDays(5),
        'return_date' => null,
        'fine_amount' => 0,
        'status' => TransactionStatus::ACTIVE,
    ]);

    $this->actingAs($user)->patch("/transactions/{$transaction->id}/return");

    $transaction->refresh();
    expect($transaction->fine_amount)->toBeGreaterThan(0);
});

it('cannot return an already returned transaction', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'borrow_date' => Carbon::now()->subDays(10),
        'due_date' => Carbon::now()->subDays(1),
        'return_date' => Carbon::now(),
        'fine_amount' => 0,
        'status' => TransactionStatus::RETURNED,
    ]);

    $response = $this->actingAs($user)->patch("/transactions/{$transaction->id}/return");

    $response->assertSessionHas('error');
});

it('prevents a different user from returning someone elses transaction', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $transaction = Transaction::create([
        'user_id' => $owner->id,
        'copy_id' => $copy->id,
        'borrow_date' => Carbon::now()->subDays(2),
        'due_date' => Carbon::now()->addDays(12),
        'return_date' => null,
        'fine_amount' => 0,
        'status' => TransactionStatus::ACTIVE,
    ]);

    $response = $this->actingAs($stranger)->patch("/transactions/{$transaction->id}/return");

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'status' => TransactionStatus::ACTIVE->value,
    ]);
});