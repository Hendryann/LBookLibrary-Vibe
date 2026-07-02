<?php

namespace App\Repositories;

use App\Models\Book;
use App\Models\Transaction;
use App\Models\Reservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecommendationRepository
{
    /**
     * Returns a [category_id => frequency] map built from the user's
     * borrowing history (all transactions, regardless of status).
     */
    public function getBorrowedCategoryFrequency(int $userId): Collection
    {
        $categoryIds = DB::table('transactions')
            ->join('book_copies', 'transactions.copy_id', '=', 'book_copies.id')
            ->join('book_category', 'book_copies.book_id', '=', 'book_category.book_id')
            ->where('transactions.user_id', $userId)
            ->pluck('book_category.category_id');

        return collect($categoryIds)
            ->countBy()
            ->sortDesc();
    }

    /**
     * Book IDs the user has already borrowed or currently has reserved,
     * so they are not recommended again.
     */
    public function getExcludedBookIds(int $userId): array
    {
        $borrowedBookIds = DB::table('transactions')
            ->join('book_copies', 'transactions.copy_id', '=', 'book_copies.id')
            ->where('transactions.user_id', $userId)
            ->pluck('book_copies.book_id');

        $reservedBookIds = Reservation::where('user_id', $userId)
            ->whereIn('status', [
                \App\Enums\ReservationStatus::PENDING,
                \App\Enums\ReservationStatus::FULFILLED,
            ])
            ->pluck('book_id');

        return $borrowedBookIds->merge($reservedBookIds)->unique()->values()->all();
    }

    public function getBooksByCategories(array $categoryIds, array $excludedBookIds): Collection
    {
        return Book::with(['categories', 'author'])
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->whereNotIn('id', $excludedBookIds)
            ->get();
    }

    /**
     * Fallback when the user has no borrowing history yet:
     * most-reviewed / highest-rated books, excluding ones already
     * borrowed or reserved (normally none, since history is empty).
     */
    public function getFallbackPopularBooks(int $userId, int $limit): Collection
    {
        $excludedBookIds = $this->getExcludedBookIds($userId);

        return Book::with(['categories', 'author'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->whereNotIn('id', $excludedBookIds)
            ->orderByDesc('reviews_count')
            ->orderByDesc('reviews_avg_rating')
            ->limit($limit)
            ->get()
            ->map(function ($book) {
                $book->setAttribute('recommendation_score', $book->reviews_count ?? 0);

                return $book;
            });
    }
}