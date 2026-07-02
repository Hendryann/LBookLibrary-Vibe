<?php

namespace App\Services;

use App\Repositories\RecommendationRepository;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function __construct(
        protected RecommendationRepository $recommendationRepository
    ) {}

    /**
     * Generate book recommendations using category-frequency scoring
     * based on the user's borrowing history, excluding books the user
     * has already borrowed or currently has reserved.
     *
     * This method is read-only and does not modify any existing data.
     */
    public function recommendForUser(int $userId, int $limit = 10): Collection
    {
        $categoryFrequency = $this->recommendationRepository->getBorrowedCategoryFrequency($userId);

        if ($categoryFrequency->isEmpty()) {
            return $this->recommendationRepository->getFallbackPopularBooks($userId, $limit);
        }

        $excludedBookIds = $this->recommendationRepository->getExcludedBookIds($userId);

        $candidateBooks = $this->recommendationRepository->getBooksByCategories(
            $categoryFrequency->keys()->all(),
            $excludedBookIds
        );

        return $candidateBooks
            ->map(function ($book) use ($categoryFrequency) {
                $score = $book->categories
                    ->sum(fn ($category) => $categoryFrequency->get($category->id, 0));

                $book->setAttribute('recommendation_score', $score);

                return $book;
            })
            ->sortByDesc('recommendation_score')
            ->take($limit)
            ->values();
    }
}