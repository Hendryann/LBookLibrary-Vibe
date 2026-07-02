<?php

use App\Enums\TransactionStatus;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Services\BorrowingHistoryService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->service = new BorrowingHistoryService(new TransactionRepository());
});

it('groups transactions by status correctly', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->create(['book_id' => $book->id]);

    Transaction::factory()->create([
        'user_id' => $user->id, 'copy_id' => $copy->id,
        'status' => TransactionStatus::ACTIVE, 'fine_amount' => 0,
        'borrow_date' => now(), 'due_date' => now()->addDays(7),
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id, 'copy_id' => $copy->id,
        'status' => TransactionStatus::OVERDUE, 'fine_amount' => 10000,
        'borrow_date' => now()->subDays(20), 'due_date' => now()->subDays(5),
    ]);

    $history = $this->service->getHistoryForUser($user->id);

    expect($history['active'])->toHaveCount(1)
        ->and($history['overdue'])->toHaveCount(1)
        ->and($history['returned'])->toHaveCount(0)
        ->and($history['total_fines'])->toEqual(10000);
});

it('returns empty groups for a user with no transactions', function () {
    $user = User::factory()->create();

    $history = $this->service->getHistoryForUser($user->id);

    expect($history['active'])->toHaveCount(0)
        ->and($history['returned'])->toHaveCount(0)
        ->and($history['overdue'])->toHaveCount(0)
        ->and($history['total_fines'])->toEqual(0);
});