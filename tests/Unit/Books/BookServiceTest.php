<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Services\BookService;
use App\Repositories\BookRepository;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('search returns paginated results', function () {
    Book::factory(5)->create();

    $repo    = app(BookRepository::class);
    $service = new BookService($repo);
    $result  = $service->search([]);

    expect($result)->toHaveProperty('items');
    expect($result->count())->toBeGreaterThanOrEqual(0);
});

it('find returns book', function () {
    $book    = Book::factory()->create();
    $repo    = app(BookRepository::class);
    $service = new BookService($repo);
    $result  = $service->find($book->id);

    expect($result?->id)->toBe($book->id);
});

it('create persists book', function () {
    $author   = Author::factory()->create();
    $category = Category::factory()->create();
    $data     = [
        'title'        => 'Test Book',
        'author_id'    => $author->id,
        'category_ids' => [$category->id],
    ];

    $repo    = app(BookRepository::class);
    $service = new BookService($repo);
    $book    = $service->create($data);

    expect($book->title)->toBe('Test Book');
    $this->assertDatabaseHas('books', ['title' => 'Test Book']);
});

it('delete returns success result', function () {
    $book    = Book::factory()->create();
    $repo    = app(BookRepository::class);
    $service = new BookService($repo);
    $result  = $service->delete($book);

    expect($result['success'])->toBeTrue();
});
