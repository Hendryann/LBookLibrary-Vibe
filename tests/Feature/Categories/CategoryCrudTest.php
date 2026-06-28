<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// INDEX
it('shows the categories index', function () {
    $this->get(route('categories.index'))->assertStatus(200);
});

// SHOW
it('shows a category detail page', function () {
    $cat = Category::factory()->create();
    $this->get(route('categories.show', $cat->id))->assertStatus(200)->assertSee($cat->name);
});

it('returns 404 for non-existent category', function () {
    $this->get(route('categories.show', 9999))->assertStatus(404);
});

// STORE
it('admin can create a category', function () {
    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::ADMIN]))
        ->post(route('categories.store'), ['name' => 'Fiction'])
        ->assertRedirect();

    $this->assertDatabaseHas('categories', ['name' => 'Fiction']);
});

it('validates name uniqueness on category store', function () {
    Category::factory()->create(['name' => 'Science']);

    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::ADMIN]))
        ->post(route('categories.store'), ['name' => 'Science'])
        ->assertSessionHasErrors('name');
});

it('member cannot create category', function () {
    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::MEMBER]))
        ->post(route('categories.store'), ['name' => 'X'])
        ->assertStatus(403);
});

// UPDATE
it('admin can update a category', function () {
    $cat = Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::ADMIN]))
        ->put(route('categories.update', $cat->id), ['name' => 'Updated Cat'])
        ->assertRedirect();

    $this->assertDatabaseHas('categories', ['id' => $cat->id, 'name' => 'Updated Cat']);
});

it('name uniqueness ignores same category on update', function () {
    $cat = Category::factory()->create(['name' => 'Thriller']);

    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::ADMIN]))
        ->put(route('categories.update', $cat->id), ['name' => 'Thriller'])
        ->assertRedirect();

    $this->assertDatabaseHas('categories', ['id' => $cat->id, 'name' => 'Thriller']);
});

// DELETE
it('admin can delete a category', function () {
    $cat = Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => \App\Enums\Role::ADMIN]))
        ->delete(route('categories.destroy', $cat->id))
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
});

// BOOKS
it('shows books in a category', function () {
    $cat  = Category::factory()->create();
    $book = Book::factory()->create();
    $book->categories()->sync([$cat->id]);

    $this->get(route('categories.books', $cat->id))->assertStatus(200)->assertSee($book->title);
});
