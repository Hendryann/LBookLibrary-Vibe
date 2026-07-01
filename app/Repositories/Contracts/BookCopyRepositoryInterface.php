<?php

namespace App\Repositories\Contracts;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Support\Collection;

interface BookCopyRepositoryInterface
{
    public function findByBook(int $bookId): Collection;
    public function findByIdAndBook(int $copyId, int $bookId): ?BookCopy;
    public function create(int $bookId, array $data): BookCopy;
    public function update(BookCopy $copy, array $data): BookCopy;
    public function delete(BookCopy $copy): bool;
    public function getAvailabilityStats(int $bookId): array;
    public function barcodeExists(string $barcode, ?int $excludeId = null): bool;
}
