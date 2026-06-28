<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// INDEX
it('shows the authors index page', function () {
    $this->get(route('authors.index'))->assertStatus(200);
});

// SHOW
it('shows an author detail page', function () {
    $author = Author::factory()->create();
    $this->get(route('authors.show', $author->id))->assertStatus(200)->assertSee($author->name);
});

it('returns 404 for non-existent author', function () {
    $this->get(route('authors.show', 9999))->assertStatus(404);
});

// STORE
it('admin can create an author', function () {
    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::ADMIN]))
        ->post(route('authors.store'), ['name' => 'Jane Doe', 'biography' => 'Bio.'])
        ->assertRedirect();

    $this->assertDatabaseHas('authors', ['name' => 'Jane Doe']);
});

it('validates name required on author store', function () {
    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::ADMIN]))
        ->post(route('authors.store'), [])
        ->assertSessionHasErrors('name');
});

it('member cannot create author', function () {
    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::MEMBER]))
        ->post(route('authors.store'), ['name' => 'X'])
        ->assertStatus(403);
});

// UPDATE
it('librarian can update an author', function () {
    $author = Author::factory()->create();

    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::LIBRARIAN]))
        ->put(route('authors.update', $author->id), ['name' => 'Updated Name'])
        ->assertRedirect();

    $this->assertDatabaseHas('authors', ['id' => $author->id, 'name' => 'Updated Name']);
});

// DELETE
it('admin can delete an author without books', function () {
    $author = Author::factory()->create();

    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::ADMIN]))
        ->delete(route('authors.destroy', $author->id))
        ->assertRedirect(route('authors.index'));

    $this->assertDatabaseMissing('authors', ['id' => $author->id]);
});

it('cannot delete author with associated books', function () {
    $author = Author::factory()->create();
    Book::factory()->create(['author_id' => $author->id]);

    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::ADMIN]))
        ->delete(route('authors.destroy', $author->id))
        ->assertRedirect();

    $this->assertDatabaseHas('authors', ['id' => $author->id]);
});

// BOOKS
it('shows books for an author', function () {
    $author = Author::factory()->create();
    $book   = Book::factory()->create(['author_id' => $author->id]);

    $this->get(route('authors.books', $author->id))->assertStatus(200)->assertSee($book->title);
});
