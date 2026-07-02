<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;

class ReviewRepository
{
    public function findByUserAndBook(int $userId, int $bookId): ?Review
    {
        return Review::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();
    }

    public function find(int $id): ?Review
    {
        return Review::find($id);
    }

    public function create(array $data): Review
    {
        return Review::create($data);
    }

    public function delete(Review $review): bool
    {
        return (bool) $review->delete();
    }

    public function getForBook(int $bookId): Collection
    {
        return Review::with('user')
            ->where('book_id', $bookId)
            ->orderByDesc('created_at')
            ->get();
    }
}