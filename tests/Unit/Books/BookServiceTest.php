<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Repositories\BookRepository;
use App\Services\BookService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('delegates search to the repository', function () {
    $repo = Mockery::mock(BookRepository::class);
    $repo->shouldReceive('search')
        ->once()
        ->with(null, null, 'title', 'asc', 12)
        ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12));

    $service = new BookService($repo);
    $service->search([]);
});

it('delegates find to the repository', function () {
    $book = Book::factory()->create();
    $repo = Mockery::mock(BookRepository::class);
    $repo->shouldReceive('findById')->once()->with($book->id)->andReturn($book);

    $service = new BookService($repo);
    $result  = $service->find($book->id);
    expect($result->id)->toBe($book->id);
});

it('delegates create to the repository', function () {
    $author   = Author::factory()->create();
    $category = Category::factory()->create();
    $data     = ['title' => 'Test Book', 'author_id' => $author->id, 'category_ids' => [$category->id]];

    $repo = Mockery::mock(BookRepository::class);
    $repo->shouldReceive('create')->once()->with($data)->andReturn(Book::factory()->make($data));

    $service = new BookService($repo);
    $service->create($data);
});

it('delete returns success result', function () {
    $book    = Book::factory()->create();
    $repo    = Mockery::mock(BookRepository::class);
    $repo->shouldReceive('delete')->once()->with($book)->andReturn(true);

    $service = new BookService($repo);
    $result  = $service->delete($book);

    expect($result['success'])->toBeTrue();
});
