<?php

use App\Models\Book;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('create persists category', function () {
    $repo     = app(CategoryRepository::class);
    $service  = new CategoryService($repo);
    $category = $service->create(['name' => 'History']);

    expect($category->name)->toBe('History');
    $this->assertDatabaseHas('categories', ['name' => 'History']);
});

it('update modifies category name', function () {
    $category = Category::factory()->create(['name' => 'Old']);
    $repo     = app(CategoryRepository::class);
    $service  = new CategoryService($repo);
    $updated  = $service->update($category, ['name' => 'New']);

    expect($updated->name)->toBe('New');
});

it('delete removes category and detaches books', function () {
    $category = Category::factory()->create();
    $book     = Book::factory()->create();
    $book->categories()->sync([$category->id]);

    $repo    = app(CategoryRepository::class);
    $service = new CategoryService($repo);
    $result  = $service->delete($category);

    expect($result['success'])->toBeTrue();
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

it('list returns paginated categories', function () {
    Category::factory(5)->create();

    $repo    = app(CategoryRepository::class);
    $service = new CategoryService($repo);
    $result  = $service->list();

    expect($result->count())->toBeGreaterThanOrEqual(0);
});
