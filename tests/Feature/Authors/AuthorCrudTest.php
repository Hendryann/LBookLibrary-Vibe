<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use App\Enums\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows authors index', function () {
    $this->get(route('authors.index'))->assertStatus(200);
});

it('shows author detail', function () {
    $author = Author::factory()->create();
    $this->get(route('authors.show', $author))->assertStatus(200)->assertSee($author->name);
});

it('returns 404 for missing author', function () {
    $this->get(route('authors.show', 9999))->assertStatus(404);
});

it('admin creates author', function () {
    $this->actingAs(User::factory()->create(['role' => Role::ADMIN]))
        ->post(route('authors.store'), ['name' => 'Jane Doe', 'biography' => 'Bio.'])
        ->assertRedirect();

    $this->assertDatabaseHas('authors', ['name' => 'Jane Doe']);
});

it('validates name required', function () {
    $this->actingAs(User::factory()->create(['role' => Role::ADMIN]))
        ->post(route('authors.store'), [])
        ->assertSessionHasErrors('name');
});

it('member forbidden from create', function () {
    $this->actingAs(User::factory()->create(['role' => Role::MEMBER]))
        ->post(route('authors.store'), ['name' => 'X'])
        ->assertStatus(403);
});

it('librarian updates author', function () {
    $author = Author::factory()->create();

    $this->actingAs(User::factory()->create(['role' => Role::LIBRARIAN]))
        ->put(route('authors.update', $author), ['name' => 'Updated Name'])
        ->assertRedirect();

    $this->assertDatabaseHas('authors', ['id' => $author->id, 'name' => 'Updated Name']);
});

it('admin deletes author without books', function () {
    $author = Author::factory()->create();

    $this->actingAs(User::factory()->create(['role' => Role::ADMIN]))
        ->delete(route('authors.destroy', $author))
        ->assertRedirect(route('authors.index'));

    $this->assertDatabaseMissing('authors', ['id' => $author->id]);
});

it('cannot delete author with books', function () {
    $author = Author::factory()->create();
    Book::factory()->create(['author_id' => $author->id]);

    $this->actingAs(User::factory()->create(['role' => Role::ADMIN]))
        ->delete(route('authors.destroy', $author))
        ->assertRedirect();

    $this->assertDatabaseHas('authors', ['id' => $author->id]);
});

it('shows books for author', function () {
    $author = Author::factory()->create();
    $book   = Book::factory()->create(['author_id' => $author->id]);

    $this->get(route('authors.books', $author))->assertStatus(200)->assertSee($book->title);
});