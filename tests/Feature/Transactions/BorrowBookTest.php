<?php

use App\Enums\CopyStatus;
use App\Enums\TransactionStatus;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeBookWithCopy(string $status = 'AVAILABLE'): array
{
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copy = BookCopy::factory()->create([
        'book_id' => $book->id,
        'status' => CopyStatus::from($status),
    ]);

    return [$book, $copy];
}

it('allows an authenticated user to borrow an available copy', function () {
    $user = User::factory()->create();
    [$book, $copy] = makeBookWithCopy('AVAILABLE');

    $response = $this->actingAs($user)->post('/transactions/borrow', [
        'copy_id' => $copy->id,
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'status' => TransactionStatus::ACTIVE->value,
    ]);

    $this->assertDatabaseHas('book_copies', [
        'id' => $copy->id,
        'status' => CopyStatus::BORROWED->value,
    ]);
});

it('rejects borrowing an unavailable copy', function () {
    $user = User::factory()->create();
    [$book, $copy] = makeBookWithCopy('BORROWED');

    $response = $this->actingAs($user)->post('/transactions/borrow', [
        'copy_id' => $copy->id,
    ]);

    $response->assertSessionHas('error');
    $this->assertDatabaseMissing('transactions', [
        'copy_id' => $copy->id,
        'user_id' => $user->id,
    ]);
});

it('rejects borrowing a non-existent copy', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/transactions/borrow', [
        'copy_id' => 999999,
    ]);

    $response->assertSessionHasErrors('copy_id');
});

it('rejects duplicate active borrowing of the same book by the same user', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    $book = Book::factory()->create(['author_id' => $author->id]);
    $copyOne = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);
    $copyTwo = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    $this->actingAs($user)->post('/transactions/borrow', ['copy_id' => $copyOne->id]);

    $response = $this->actingAs($user)->post('/transactions/borrow', ['copy_id' => $copyTwo->id]);

    $response->assertSessionHas('error');
});

it('prevents guests from borrowing', function () {
    [$book, $copy] = makeBookWithCopy('AVAILABLE');

    $response = $this->post('/transactions/borrow', ['copy_id' => $copy->id]);

    $response->assertRedirect('/login');
});