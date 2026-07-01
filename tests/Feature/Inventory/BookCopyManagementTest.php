<?php

use App\Enums\CopyStatus;
use App\Enums\Role;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ────────────────────────────────────────────────────────────────

function makeAdmin(): User
{
    return User::factory()->create(['role' => Role::ADMIN]);
}

function makeLibrarian(): User
{
    return User::factory()->create(['role' => Role::LIBRARIAN]);
}

function makeMember(): User
{
    return User::factory()->create(['role' => Role::MEMBER]);
}

function makeBook(): Book
{
    $author = Author::factory()->create();
    return Book::factory()->create(['author_id' => $author->id]);
}

// ─── View Copies (GET /books/{book}/copies) ──────────────────────────────────

test('admin can view book copies', function () {
    $admin = makeAdmin();
    $book  = makeBook();
    BookCopy::factory()->count(3)->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    $this->actingAs($admin)
        ->get(route('books.copies.index', $book))
        ->assertOk()
        ->assertViewIs('books.show')
        ->assertViewHas('copies');
});

test('member can view book copies', function () {
    $member = makeMember();
    $book   = makeBook();

    $this->actingAs($member)
        ->get(route('books.copies.index', $book))
        ->assertOk()
        ->assertViewIs('books.show');
});

test('guest cannot view book copies', function () {
    $book = makeBook();

    $this->get(route('books.copies.index', $book))
        ->assertRedirect(route('login'));
});

// ─── Create Copy (POST /books/{book}/copies) ─────────────────────────────────

test('admin can create a book copy', function () {
    $admin = makeAdmin();
    $book  = makeBook();

    $this->actingAs($admin)
        ->post(route('books.copies.store', $book), ['status' => 'AVAILABLE'])
        ->assertRedirect(route('books.copies.index', $book))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('book_copies', ['book_id' => $book->id, 'status' => 'AVAILABLE']);
});

test('librarian can create a book copy', function () {
    $librarian = makeLibrarian();
    $book      = makeBook();

    $this->actingAs($librarian)
        ->post(route('books.copies.store', $book), ['status' => 'AVAILABLE'])
        ->assertRedirect(route('books.copies.index', $book));

    $this->assertDatabaseHas('book_copies', ['book_id' => $book->id]);
});

test('member cannot create a book copy', function () {
    $member = makeMember();
    $book   = makeBook();

    $this->actingAs($member)
        ->post(route('books.copies.store', $book), ['status' => 'AVAILABLE'])
        ->assertForbidden();
});

test('create copy with invalid status returns validation error', function () {
    $admin = makeAdmin();
    $book  = makeBook();

    $this->actingAs($admin)
        ->post(route('books.copies.store', $book), ['status' => 'INVALID_STATUS'])
        ->assertSessionHasErrors('status');
});

test('create copy defaults to AVAILABLE when no status provided', function () {
    $admin = makeAdmin();
    $book  = makeBook();

    $this->actingAs($admin)
        ->post(route('books.copies.store', $book), [])
        ->assertRedirect(route('books.copies.index', $book));

    $this->assertDatabaseHas('book_copies', ['book_id' => $book->id, 'status' => 'AVAILABLE']);
});

test('cannot create copy for non-existent book', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->post(route('books.copies.store', 99999), ['status' => 'AVAILABLE'])
        ->assertNotFound();
});

// ─── Update Copy (PUT /books/{book}/copies/{copyId}) ─────────────────────────

test('admin can update book copy status', function () {
    $admin = makeAdmin();
    $book  = makeBook();
    $copy  = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    $this->actingAs($admin)
        ->put(route('books.copies.update', [$book, $copy->id]), ['status' => 'BORROWED'])
        ->assertRedirect(route('books.copies.index', $book))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('book_copies', ['id' => $copy->id, 'status' => 'BORROWED']);
});

test('member cannot update book copy status', function () {
    $member = makeMember();
    $book   = makeBook();
    $copy   = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    $this->actingAs($member)
        ->put(route('books.copies.update', [$book, $copy->id]), ['status' => 'BORROWED'])
        ->assertForbidden();
});

test('update copy with missing status returns validation error', function () {
    $admin = makeAdmin();
    $book  = makeBook();
    $copy  = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    $this->actingAs($admin)
        ->put(route('books.copies.update', [$book, $copy->id]), [])
        ->assertSessionHasErrors('status');
});

test('cannot update status of lost copy', function () {
    $admin = makeAdmin();
    $book  = makeBook();
    $copy  = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::LOST]);

    $this->actingAs($admin)
        ->put(route('books.copies.update', [$book, $copy->id]), ['status' => 'AVAILABLE'])
        ->assertRedirect(route('books.copies.index', $book))
        ->assertSessionHas('error');

    // Status must not have changed
    $this->assertDatabaseHas('book_copies', ['id' => $copy->id, 'status' => 'LOST']);
});

// ─── Delete Copy (DELETE /books/{book}/copies/{copyId}) ──────────────────────

test('admin can delete a book copy', function () {
    $admin = makeAdmin();
    $book  = makeBook();
    $copy  = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    $this->actingAs($admin)
        ->delete(route('books.copies.destroy', [$book, $copy->id]))
        ->assertRedirect(route('books.copies.index', $book))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('book_copies', ['id' => $copy->id]);
});

test('member cannot delete a book copy', function () {
    $member = makeMember();
    $book   = makeBook();
    $copy   = BookCopy::factory()->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);

    $this->actingAs($member)
        ->delete(route('books.copies.destroy', [$book, $copy->id]))
        ->assertForbidden();

    $this->assertDatabaseHas('book_copies', ['id' => $copy->id]);
});

test('delete non-existent copy returns 404', function () {
    $admin = makeAdmin();
    $book  = makeBook();

    $this->actingAs($admin)
        ->delete(route('books.copies.destroy', [$book, 99999]))
        ->assertNotFound();
});

// ─── Availability (GET /books/{book}/availability) ────────────────────────────

test('availability page is accessible to members', function () {
    $member = makeMember();
    $book   = makeBook();

    $this->actingAs($member)
        ->get(route('books.availability', $book))
        ->assertOk()
        ->assertViewIs('books.availability')
        ->assertViewHas('availability');
});

test('availability reflects current inventory state', function () {
    $admin = makeAdmin();
    $book  = makeBook();

    BookCopy::factory()->count(2)->create(['book_id' => $book->id, 'status' => CopyStatus::AVAILABLE]);
    BookCopy::factory()->count(1)->create(['book_id' => $book->id, 'status' => CopyStatus::BORROWED]);

    $response = $this->actingAs($admin)->get(route('books.availability', $book));
    $availability = $response->viewData('availability');

    expect($availability['total'])->toBe(3)
        ->and($availability['available'])->toBe(2)
        ->and($availability['borrowed'])->toBe(1)
        ->and($availability['reserved'])->toBe(0)
        ->and($availability['lost'])->toBe(0);
});
