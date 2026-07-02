<?php

use App\Enums\Role;
use App\Enums\TransactionStatus;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Transaction;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('retrieves borrowing history for the authenticated user', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->create(['book_id' => $book->id]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'status' => TransactionStatus::ACTIVE,
        'borrow_date' => now(),
        'due_date' => now()->addDays(7),
        'fine_amount' => 0,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'status' => TransactionStatus::RETURNED,
        'borrow_date' => now()->subDays(10),
        'due_date' => now()->subDays(3),
        'return_date' => now()->subDays(2),
        'fine_amount' => 5000,
    ]);

    $response = $this->actingAs($user)->get(route('users.history'));

    $response->assertOk();
    $response->assertViewHas('history', function ($history) {
        return $history['active']->count() === 1
            && $history['returned']->count() === 1
            && $history['total_fines'] == 5000;
    });
});

it('does not leak another users transactions in history', function () {
    $userA = User::factory()->create(['role' => Role::MEMBER]);
    $userB = User::factory()->create(['role' => Role::MEMBER]);
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->create(['book_id' => $book->id]);

    Transaction::factory()->create([
        'user_id' => $userB->id,
        'copy_id' => $copy->id,
        'status' => TransactionStatus::ACTIVE,
        'borrow_date' => now(),
        'due_date' => now()->addDays(7),
        'fine_amount' => 0,
    ]);

    $response = $this->actingAs($userA)->get(route('users.history'));

    $response->assertOk();
    $response->assertViewHas('history', function ($history) {
        return $history['active']->isEmpty()
            && $history['returned']->isEmpty()
            && $history['overdue']->isEmpty();
    });
});