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

function createActiveTransaction(User $user, string $dueOffset = '+11 days'): Transaction
{
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    return Transaction::create([
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'borrow_date' => Carbon::now()->subDays(3),
        'due_date' => Carbon::parse($dueOffset),
        'return_date' => null,
        'fine_amount' => 0,
        'status' => TransactionStatus::ACTIVE,
    ]);
}

it('extends an active transaction due date', function () {
    $user = User::factory()->create();
    $transaction = createActiveTransaction($user);
    $originalDue = $transaction->due_date;

    $response = $this->actingAs($user)->patch("/transactions/{$transaction->id}/extend");

    $response->assertSessionHas('success');
    $transaction->refresh();
    expect($transaction->due_date->toDateString())
        ->toBe($originalDue->copy()->addDays(7)->toDateString());
});

it('cannot extend a returned transaction', function () {
    $user = User::factory()->create();
    $transaction = createActiveTransaction($user);
    $transaction->update(['status' => TransactionStatus::RETURNED, 'return_date' => now()]);

    $response = $this->actingAs($user)->patch("/transactions/{$transaction->id}/extend");

    $response->assertSessionHas('error');
});

it('cannot extend an overdue transaction', function () {
    $user = User::factory()->create();
    $transaction = createActiveTransaction($user, '-2 days');

    $response = $this->actingAs($user)->patch("/transactions/{$transaction->id}/extend");

    $response->assertSessionHas('error');
});