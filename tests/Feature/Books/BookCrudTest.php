<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Helpers
function adminUser(): User
{
    return User::factory()->create(['role' => \App\Enums\Role::ADMIN]);
}

function librarianUser(): User
{
    return User::factory()->create(['role' => \App\Enums\Role::LIBRARIAN]);
}

function memberUser(): User
{
    return User::factory()->create(['role' => \App\Enums\Role::MEMBER]);
}

// INDEX
it('shows the books index page', function () {
    $this->get(route('books.index'))->assertStatus(200);
});

it('shows books on the index page', function () {
    $book = Book::factory()->create();
    $this->get(route('books.index'))->assertSee($book->title);
});

// SHOW
it('shows a single book', function () {
    $book = Book::factory()->create();
    $this->get(route('books.show', $book->id))->assertStatus(200)->assertSee($book->title);
});

it('returns 404 for non-existent book', function () {
    $this->get(route('books.show', 9999))->assertStatus(404);
});

// CREATE
it('redirects guests from book create page', function () {
    $this->get(route('books.create'))->assertRedirect(route('login'));
});

it('shows create form to admin', function () {
    $this->actingAs(adminUser())->get(route('books.create'))->assertStatus(200);
});

it('returns 403 for member on create form', function () {
    $this->actingAs(memberUser())->get(route('books.create'))->assertStatus(403);
});

// STORE
it('admin can create a book', function () {
    $author   = Author::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs(adminUser())->post(route('books.store'), [
        'title'            => 'New Book',
        'author_id'        => $author->id,
        'category_ids'     => [$category->id],
        'publication_year' => 2023,
        'isbn'             => '9781234567890',
    ])->assertRedirect();

    $this->assertDatabaseHas('books', ['title' => 'New Book']);
});

it('librarian can create a book', function () {
    $author = Author::factory()->create();

    $this->actingAs(librarianUser())->post(route('books.store'), [
        'title'     => 'Librarian Book',
        'author_id' => $author->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('books', ['title' => 'Librarian Book']);
});

it('member cannot create a book', function () {
    $author = Author::factory()->create();

    $this->actingAs(memberUser())->post(route('books.store'), [
        'title'     => 'Member Book',
        'author_id' => $author->id,
    ])->assertStatus(403);
});

it('validates required fields on store', function () {
    $this->actingAs(adminUser())->post(route('books.store'), [])
        ->assertSessionHasErrors(['title', 'author_id']);
});

it('validates ISBN uniqueness on store', function () {
    $author      = Author::factory()->create();
    $existingIsbn = '9780000000001';
    Book::factory()->create(['isbn' => $existingIsbn]);

    $this->actingAs(adminUser())->post(route('books.store'), [
        'title'     => 'Duplicate ISBN Book',
        'author_id' => $author->id,
        'isbn'      => $existingIsbn,
    ])->assertSessionHasErrors('isbn');
});

it('validates author must exist on store', function () {
    $this->actingAs(adminUser())->post(route('books.store'), [
        'title'     => 'Book',
        'author_id' => 99999,
    ])->assertSessionHasErrors('author_id');
});

// UPDATE
it('admin can update a book', function () {
    $book   = Book::factory()->create();
    $author = Author::factory()->create();

    $this->actingAs(adminUser())->put(route('books.update', $book->id), [
        'title'     => 'Updated Title',
        'author_id' => $author->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Title']);
});

it('member cannot update a book', function () {
    $book   = Book::factory()->create();
    $author = Author::factory()->create();

    $this->actingAs(memberUser())->put(route('books.update', $book->id), [
        'title'     => 'Updated',
        'author_id' => $author->id,
    ])->assertStatus(403);
});

it('ISBN uniqueness ignores the same book on update', function () {
    $book   = Book::factory()->create(['isbn' => '9781111111111']);
    $author = Author::factory()->create();

    $this->actingAs(adminUser())->put(route('books.update', $book->id), [
        'title'     => $book->title,
        'author_id' => $author->id,
        'isbn'      => '9781111111111',
    ])->assertRedirect();

    $this->assertDatabaseHas('books', ['id' => $book->id, 'isbn' => '9781111111111']);
});

// DELETE
it('admin can delete a book', function () {
    $book = Book::factory()->create();

    $this->actingAs(adminUser())->delete(route('books.destroy', $book->id))
        ->assertRedirect(route('books.index'));

    $this->assertDatabaseMissing('books', ['id' => $book->id]);
});

it('member cannot delete a book', function () {
    $book = Book::factory()->create();

    $this->actingAs(memberUser())->delete(route('books.destroy', $book->id))
        ->assertStatus(403);
});

// SEARCH & FILTER
it('can search books by title', function () {
    Book::factory()->create(['title' => 'PHP for Beginners']);
    Book::factory()->create(['title' => 'Advanced Laravel']);

    $this->get(route('books.index', ['q' => 'PHP']))->assertSee('PHP for Beginners')->assertDontSee('Advanced Laravel');
});

it('can filter books by category', function () {
    $cat  = Category::factory()->create();
    $book = Book::factory()->create();
    $book->categories()->sync([$cat->id]);

    $other = Book::factory()->create();

    $this->get(route('books.index', ['category' => $cat->id]))->assertSee($book->title);
});

it('can sort books by publication year', function () {
    $old  = Book::factory()->create(['publication_year' => 2000, 'title' => 'Old Book']);
    $new  = Book::factory()->create(['publication_year' => 2023, 'title' => 'New Book']);

    $response = $this->get(route('books.index', ['sort_by' => 'publication_year', 'sort_dir' => 'desc']));
    $response->assertSeeInOrder(['New Book', 'Old Book']);
});
