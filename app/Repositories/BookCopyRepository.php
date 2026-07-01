<?php

namespace App\Repositories;

use App\Enums\CopyStatus;
use App\Models\BookCopy;
use App\Repositories\Contracts\BookCopyRepositoryInterface;
use Illuminate\Support\Collection;

class BookCopyRepository implements BookCopyRepositoryInterface
{
    public function findByBook(int $bookId): Collection
    {
        return BookCopy::where('book_id', $bookId)
            ->orderBy('id')
            ->get();
    }

    public function findByIdAndBook(int $copyId, int $bookId): ?BookCopy
    {
        return BookCopy::where('id', $copyId)
            ->where('book_id', $bookId)
            ->first();
    }

    public function create(int $bookId, array $data): BookCopy
    {
        return BookCopy::create([
            'book_id' => $bookId,
            'status'  => $data['status'] ?? CopyStatus::AVAILABLE,
        ]);
    }

    public function update(BookCopy $copy, array $data): BookCopy
    {
        $copy->update(['status' => $data['status']]);
        return $copy->fresh();
    }

    public function delete(BookCopy $copy): bool
    {
        return $copy->delete();
    }

    public function getAvailabilityStats(int $bookId): array
    {
        $copies = BookCopy::where('book_id', $bookId)->get();

        return [
            'total'     => $copies->count(),
            'available' => $copies->where('status', CopyStatus::AVAILABLE)->count(),
            'borrowed'  => $copies->where('status', CopyStatus::BORROWED)->count(),
            'reserved'  => $copies->where('status', CopyStatus::RESERVED)->count(),
            'lost'      => $copies->where('status', CopyStatus::LOST)->count(),
        ];
    }

    public function barcodeExists(string $barcode, ?int $excludeId = null): bool
    {
        // Barcode is a virtual attribute (COPY-XXXXX = COPY-{id padded to 5})
        // Extract numeric ID from barcode
        if (!preg_match('/^COPY-(\d+)$/', $barcode, $matches)) {
            return false;
        }

        $id = (int) ltrim($matches[1], '0') ?: 0;

        $query = BookCopy::where('id', $id);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
