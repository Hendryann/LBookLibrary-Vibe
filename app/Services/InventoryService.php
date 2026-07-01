<?php

namespace App\Services;

use App\Enums\CopyStatus;
use App\Models\Book;
use App\Models\BookCopy;
use App\Repositories\Contracts\BookCopyRepositoryInterface;
use Illuminate\Support\Collection;

class InventoryService
{
    public function __construct(
        private readonly BookCopyRepositoryInterface $bookCopyRepository
    ) {}

    public function getCopiesForBook(int $bookId): Collection
    {
        return $this->bookCopyRepository->findByBook($bookId);
    }

    public function getAvailability(int $bookId): array
    {
        $stats = $this->bookCopyRepository->getAvailabilityStats($bookId);

        $stats['status'] = match (true) {
            $stats['available'] > 0 => 'available',
            $stats['borrowed'] > 0 || $stats['reserved'] > 0 => 'out_of_stock',
            $stats['total'] === 0 => 'no_copies',
            default => 'out_of_stock',
        };

        return $stats;
    }

    public function createCopy(Book $book, array $data): BookCopy
    {
        return $this->bookCopyRepository->create($book->id, $data);
    }

    public function updateCopy(BookCopy $copy, array $data): BookCopy
    {
        return $this->bookCopyRepository->update($copy, $data);
    }

    public function deleteCopy(BookCopy $copy): bool
    {
        return $this->bookCopyRepository->delete($copy);
    }

    public function isValidStatusTransition(CopyStatus $current, CopyStatus $next): bool
    {
        // Lost copies cannot be changed back to any status
        if ($current === CopyStatus::LOST) {
            return false;
        }

        // Borrowed copies can only go to AVAILABLE or LOST
        if ($current === CopyStatus::BORROWED) {
            return in_array($next, [CopyStatus::AVAILABLE, CopyStatus::LOST], true);
        }

        // Reserved copies can go to AVAILABLE, BORROWED, or LOST
        if ($current === CopyStatus::RESERVED) {
            return in_array($next, [CopyStatus::AVAILABLE, CopyStatus::BORROWED, CopyStatus::LOST], true);
        }

        // AVAILABLE can go anywhere
        return true;
    }
}