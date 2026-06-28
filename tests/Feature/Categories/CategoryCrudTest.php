<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Enums\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows categories index', function () {
    $this->get(route('categories.index'))->assertStatus(200);
});

it('shows category detail', function () {
    $cat = Category::factory()->create();
    $this->get(route('categories.show', $cat))->assertStatus(200)->assertSee($cat->name);
});

it('returns 404 for missing category', function () {
    $this->get(route('categories.show', 9999))->assertStatus(404);
});

it('admin creates category', function () {
    $this->actingAs(User::factory()->create(['role' => Role::ADMIN]))
        ->post(route('categories.store'), ['name' => 'Fiction'])
        ->assertRedirect();

    $this->assertDatabaseHas('categories', ['name' => 'Fiction']);
});

it('validates name unique', function () {
    Category::factory()->create(['name' => 'Science']);

    $this->actingAs(User::factory()->create(['role' => Role::ADMIN]))
        ->post(route('categories.store'), ['name' => 'Science'])
        ->assertSessionHasErrors('name');
});

it('member forbidden from create', function () {
    $this->actingAs(User::factory()->create(['role' => Role::MEMBER]))
        ->post(route('categories.store'), ['name' => 'X'])
        ->assertStatus(403);
});

it('admin updates category', function () {
    $cat = Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => Role::ADMIN]))
        ->put(route('categories.update', $cat), ['name' => 'Updated Cat'])
        ->assertRedirect();

    $this->assertDatabaseHas('categories', ['id' => $cat->id, 'name' => 'Updated Cat']);
});

it('admin deletes category', function () {
    $cat = Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => Role::ADMIN]))
        ->delete(route('categories.destroy', $cat))
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
});

it('shows books in category', function () {
    $cat  = Category::factory()->create();
    $book = Book::factory()->create();
    $book->categories()->sync([$cat->id]);

    $this->get(route('categories.books', $cat))->assertStatus(200)->assertSee($book->title);
});
