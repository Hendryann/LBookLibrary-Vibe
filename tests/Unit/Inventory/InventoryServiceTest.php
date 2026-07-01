<?php

use App\Enums\CopyStatus;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Author;
use App\Repositories\BookCopyRepository;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeInventoryService(): InventoryService
{
    return new InventoryService(new BookCopyRepository());
}

function makeTestBook(): Book
{
    $author = Author::factory()->create();
    return Book::factory()->create(['author_id' => $author->id]);
}

function makeTestCopy(Book $book, CopyStatus $status = CopyStatus::AVAILABLE): BookCopy
{
    return BookCopy::create([
        'book_id' => $book->id,
        'status' => $status,
    ]);
}

// ─── Availability Calculation ─────────────────────────────────────────────────

test('availability returns zero stats for book with no copies', function () {
    $service = makeInventoryService();
    $book    = makeTestBook();

    $availability = $service->getAvailability($book->id);

    expect($availability['total'])->toBe(0)
        ->and($availability['available'])->toBe(0)
        ->and($availability['borrowed'])->toBe(0)
        ->and($availability['reserved'])->toBe(0)
        ->and($availability['lost'])->toBe(0)
        ->and($availability['status'])->toBe('no_copies');
});

test('availability status is available when copies exist', function () {
    $service = makeInventoryService();
    $book    = makeTestBook();
    makeTestCopy($book, CopyStatus::AVAILABLE);

    $availability = $service->getAvailability($book->id);

    expect($availability['status'])->toBe('available')
        ->and($availability['available'])->toBe(1);
});

test('availability status is out_of_stock when all copies are borrowed', function () {
    $service = makeInventoryService();
    $book    = makeTestBook();
    makeTestCopy($book, CopyStatus::BORROWED);
    makeTestCopy($book, CopyStatus::BORROWED);

    $availability = $service->getAvailability($book->id);

    expect($availability['status'])->toBe('out_of_stock')
        ->and($availability['available'])->toBe(0);
});

test('availability is recalculated after adding a copy', function () {
    $service = makeInventoryService();
    $book    = makeTestBook();

    $before = $service->getAvailability($book->id);
    expect($before['total'])->toBe(0);

    $service->createCopy($book, ['status' => CopyStatus::AVAILABLE->value]);

    $after = $service->getAvailability($book->id);
    expect($after['total'])->toBe(1)->and($after['available'])->toBe(1);
});

test('availability is recalculated after deleting a copy', function () {
    $service = makeInventoryService();
    $book    = makeTestBook();
    $copy    = makeTestCopy($book, CopyStatus::AVAILABLE);

    $before = $service->getAvailability($book->id);
    expect($before['total'])->toBe(1);

    $service->deleteCopy($copy);

    $after = $service->getAvailability($book->id);
    expect($after['total'])->toBe(0);
});

// ─── Status Transition Validation ────────────────────────────────────────────

test('LOST copy cannot transition to any status', function () {
    $service = makeInventoryService();

    foreach (CopyStatus::cases() as $next) {
        expect($service->isValidStatusTransition(CopyStatus::LOST, $next))->toBeFalse();
    }
});

test('AVAILABLE copy can transition to any status', function () {
    $service = makeInventoryService();

    foreach (CopyStatus::cases() as $next) {
        expect($service->isValidStatusTransition(CopyStatus::AVAILABLE, $next))->toBeTrue();
    }
});

test('BORROWED copy can only transition to AVAILABLE or LOST', function () {
    $service = makeInventoryService();

    expect($service->isValidStatusTransition(CopyStatus::BORROWED, CopyStatus::AVAILABLE))->toBeTrue();
    expect($service->isValidStatusTransition(CopyStatus::BORROWED, CopyStatus::LOST))->toBeTrue();
    expect($service->isValidStatusTransition(CopyStatus::BORROWED, CopyStatus::BORROWED))->toBeFalse();
    expect($service->isValidStatusTransition(CopyStatus::BORROWED, CopyStatus::RESERVED))->toBeFalse();
});

test('RESERVED copy can transition to AVAILABLE, BORROWED, or LOST', function () {
    $service = makeInventoryService();

    expect($service->isValidStatusTransition(CopyStatus::RESERVED, CopyStatus::AVAILABLE))->toBeTrue();
    expect($service->isValidStatusTransition(CopyStatus::RESERVED, CopyStatus::BORROWED))->toBeTrue();
    expect($service->isValidStatusTransition(CopyStatus::RESERVED, CopyStatus::LOST))->toBeTrue();
    expect($service->isValidStatusTransition(CopyStatus::RESERVED, CopyStatus::RESERVED))->toBeFalse();
});

// ─── Inventory Synchronization ────────────────────────────────────────────────

test('create copy stores correct book_id and status', function () {
    $service = makeInventoryService();
    $book    = makeTestBook();

    $copy = $service->createCopy($book, ['status' => CopyStatus::BORROWED->value]);

    expect($copy->book_id)->toBe($book->id)
        ->and($copy->status)->toBe(CopyStatus::BORROWED);
});

test('update copy changes status correctly', function () {
    $service = makeInventoryService();
    $book    = makeTestBook();
    $copy    = makeTestCopy($book, CopyStatus::AVAILABLE);

    $updated = $service->updateCopy($copy, ['status' => CopyStatus::BORROWED->value]);

    expect($updated->status)->toBe(CopyStatus::BORROWED);
});

test('mixed statuses are all counted correctly', function () {
    $service = makeInventoryService();
    $book    = makeTestBook();

    makeTestCopy($book, CopyStatus::AVAILABLE);
    makeTestCopy($book, CopyStatus::AVAILABLE);
    makeTestCopy($book, CopyStatus::AVAILABLE);
    makeTestCopy($book, CopyStatus::BORROWED);
    makeTestCopy($book, CopyStatus::BORROWED);
    makeTestCopy($book, CopyStatus::RESERVED);
    makeTestCopy($book, CopyStatus::LOST);

    $stats = $service->getAvailability($book->id);

    expect($stats['total'])->toBe(7)
        ->and($stats['available'])->toBe(3)
        ->and($stats['borrowed'])->toBe(2)
        ->and($stats['reserved'])->toBe(1)
        ->and($stats['lost'])->toBe(1);
});
