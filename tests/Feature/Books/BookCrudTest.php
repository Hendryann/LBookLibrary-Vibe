<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Enums\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function adminUser(): User
{
    return User::factory()->create(['role' => Role::ADMIN]);
}

function librarianUser(): User
{
    return User::factory()->create(['role' => Role::LIBRARIAN]);
}

function memberUser(): User
{
    return User::factory()->create(['role' => Role::MEMBER]);
}

// INDEX
it('shows books index', function () {
    $this->get(route('books.index'))->assertStatus(200);
});

it('displays books on index', function () {
    $book = Book::factory()->create();
    $this->get(route('books.index'))->assertSee($book->title);
});

// SHOW
it('shows book detail', function () {
    $book = Book::factory()->create();
    $this->get(route('books.show', $book))->assertStatus(200)->assertSee($book->title);
});

it('returns 404 for missing book', function () {
    $this->get(route('books.show', 9999))->assertStatus(404);
});

// CREATE
it('guest redirected from create', function () {
    $this->get(route('books.create'))->assertRedirect(route('login'));
});

it('admin sees create form', function () {
    $this->actingAs(adminUser())->get(route('books.create'))->assertStatus(200);
});

it('member forbidden from create form', function () {
    $this->actingAs(memberUser())->get(route('books.create'))->assertStatus(403);
});

// STORE
it('admin creates book', function () {
    $author   = Author::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs(adminUser())->post(route('books.store'), [
        'title'        => 'New Book',
        'author_id'    => $author->id,
        'category_ids' => [$category->id],
    ])->assertRedirect();

    $this->assertDatabaseHas('books', ['title' => 'New Book']);
});

it('librarian creates book', function () {
    $author = Author::factory()->create();

    $this->actingAs(librarianUser())->post(route('books.store'), [
        'title'     => 'Librarian Book',
        'author_id' => $author->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('books', ['title' => 'Librarian Book']);
});

it('member forbidden from store', function () {
    $author = Author::factory()->create();

    $this->actingAs(memberUser())->post(route('books.store'), [
        'title'     => 'Member Book',
        'author_id' => $author->id,
    ])->assertStatus(403);
});

it('validates required title', function () {
    $this->actingAs(adminUser())->post(route('books.store'), [
        'author_id' => Author::factory()->id,
    ])->assertSessionHasErrors('title');
});

it('validates ISBN unique', function () {
    $author      = Author::factory()->create();
    $existingIsbn = '9780000000001';
    Book::factory()->create(['author_id' => $author->id, 'isbn' => $existingIsbn]);

    $this->actingAs(adminUser())->post(route('books.store'), [
        'title'     => 'Duplicate',
        'author_id' => $author->id,
        'isbn'      => $existingIsbn,
    ])->assertSessionHasErrors('isbn');
});

it('validates author exists', function () {
    $this->actingAs(adminUser())->post(route('books.store'), [
        'title'     => 'Book',
        'author_id' => 99999,
    ])->assertSessionHasErrors('author_id');
});

// UPDATE
it('admin updates book', function () {
    $book   = Book::factory()->create();
    $author = Author::factory()->create();

    $this->actingAs(adminUser())->put(route('books.update', $book), [
        'title'     => 'Updated Title',
        'author_id' => $author->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Title']);
});

it('member forbidden from update', function () {
    $book   = Book::factory()->create();
    $author = Author::factory()->create();

    $this->actingAs(memberUser())->put(route('books.update', $book), [
        'title'     => 'Updated',
        'author_id' => $author->id,
    ])->assertStatus(403);
});

// DELETE
it('admin deletes book', function () {
    $book = Book::factory()->create();

    $this->actingAs(adminUser())->delete(route('books.destroy', $book))
        ->assertRedirect(route('books.index'));

    $this->assertDatabaseMissing('books', ['id' => $book->id]);
});

it('member forbidden from delete', function () {
    $book = Book::factory()->create();

    $this->actingAs(memberUser())->delete(route('books.destroy', $book))
        ->assertStatus(403);
});

// SEARCH
it('searches by title', function () {
    Book::factory()->create(['title' => 'PHP Basics']);
    Book::factory()->create(['title' => 'Laravel Advanced']);

    $this->get(route('books.index', ['q' => 'PHP']))->assertSee('PHP Basics');
});

it('filters by category', function () {
    $cat  = Category::factory()->create();
    $book = Book::factory()->create();
    $book->categories()->sync([$cat->id]);

    $this->get(route('books.index', ['category' => $cat->id]))->assertSee($book->title);
});

it('sorts by publication year', function () {
    Book::factory()->create(['publication_year' => 2000, 'title' => 'Old Book']);
    Book::factory()->create(['publication_year' => 2023, 'title' => 'New Book']);

    $response = $this->get(route('books.index', ['sort_by' => 'publication_year', 'sort_dir' => 'desc']));
    $response->assertSeeInOrder(['New Book', 'Old Book']);
});
