<?php

use App\Models\Author;
use App\Models\Book;
use App\Repositories\AuthorRepository;
use App\Services\AuthorService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('delete fails when author has books', function () {
    $author = Author::factory()->create();
    Book::factory()->create(['author_id' => $author->id]);

    $repo    = app(AuthorRepository::class);
    $service = new AuthorService($repo);
    $result  = $service->delete($author);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('associated books');
});

it('delete succeeds when author has no books', function () {
    $author  = Author::factory()->create();
    $repo    = app(AuthorRepository::class);
    $service = new AuthorService($repo);
    $result  = $service->delete($author);

    expect($result['success'])->toBeTrue();
    $this->assertDatabaseMissing('authors', ['id' => $author->id]);
});

it('create persists author', function () {
    $repo    = app(AuthorRepository::class);
    $service = new AuthorService($repo);
    $author  = $service->create(['name' => 'Test Author', 'biography' => 'Bio']);

    expect($author->name)->toBe('Test Author');
    $this->assertDatabaseHas('authors', ['name' => 'Test Author']);
});

it('update modifies author fields', function () {
    $author  = Author::factory()->create(['name' => 'Old Name']);
    $repo    = app(AuthorRepository::class);
    $service = new AuthorService($repo);
    $updated = $service->update($author, ['name' => 'New Name', 'biography' => '']);

    expect($updated->name)->toBe('New Name');
});
