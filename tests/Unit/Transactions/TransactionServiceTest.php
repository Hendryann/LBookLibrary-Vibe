<?php

use App\Enums\CopyStatus;
use App\Enums\TransactionStatus;
use App\Exceptions\BorrowingException;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(TransactionService::class);
});

it('calculates zero fine when not overdue', function () {
    $transaction = new Transaction([
        'due_date' => Carbon::now()->addDays(5),
    ]);

    expect($this->service->calculateFine($transaction))->toBe(0.0);
});

it('calculates a positive fine proportional to days overdue', function () {
    $transaction = new Transaction([
        'due_date' => Carbon::now()->subDays(4),
    ]);

    $fine = $this->service->calculateFine($transaction);

    expect($fine)->toBe(round(4 * TransactionService::FINE_PER_DAY, 2));
});

it('never returns a negative fine', function () {
    $transaction = new Transaction([
        'due_date' => Carbon::now()->addDay(),
    ]);

    expect($this->service->calculateFine($transaction))->toBeGreaterThanOrEqual(0.0);
});

it('throws when borrowing an unavailable copy', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::LOST]);

    expect(fn () => $this->service->borrow($user, $copy->id))
        ->toThrow(BorrowingException::class);
});

it('throws when extending a returned transaction', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'borrow_date' => Carbon::now()->subDays(15),
        'due_date' => Carbon::now()->subDays(1),
        'return_date' => Carbon::now(),
        'fine_amount' => 0,
        'status' => TransactionStatus::RETURNED,
    ]);

    expect(fn () => $this->service->extend($user, $transaction->id))
        ->toThrow(BorrowingException::class);
});

it('syncs an active transaction to overdue once the due date has passed', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'borrow_date' => Carbon::now()->subDays(20),
        'due_date' => Carbon::now()->subDays(2),
        'return_date' => null,
        'fine_amount' => 0,
        'status' => TransactionStatus::ACTIVE,
    ]);

    $this->service->syncOverdueStatuses();

    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::OVERDUE);
    expect($transaction->fine_amount)->toBeGreaterThan(0);
});