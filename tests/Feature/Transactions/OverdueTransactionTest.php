<?php

use App\Enums\CopyStatus;
use App\Enums\Role;
use App\Enums\TransactionStatus;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('lists overdue transactions for staff and syncs status', function () {
    $librarian = User::factory()->create(['role' => Role::LIBRARIAN]);
    $borrower = User::factory()->create();
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copy = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $transaction = Transaction::create([
        'user_id' => $borrower->id,
        'copy_id' => $copy->id,
        'borrow_date' => Carbon::now()->subDays(20),
        'due_date' => Carbon::now()->subDays(3),
        'return_date' => null,
        'fine_amount' => 0,
        'status' => TransactionStatus::ACTIVE,
    ]);

    $response = $this->actingAs($librarian)->get('/transactions/overdue');

    $response->assertOk();
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'status' => TransactionStatus::OVERDUE->value,
    ]);
});

it('forbids members from viewing the overdue list', function () {
    $member = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($member)->get('/transactions/overdue');

    $response->assertForbidden();
});