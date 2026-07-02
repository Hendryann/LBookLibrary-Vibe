<?php

namespace App\Services;

use App\Enums\Role;
use App\Exceptions\BookNotFoundException;
use App\Exceptions\DuplicateReviewException;
use App\Exceptions\ReviewNotFoundException;
use App\Exceptions\UnauthorizedActionException;
use App\Models\Review;
use App\Models\User;
use App\Repositories\BookRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function __construct(
        protected ReviewRepository $reviewRepository,
        protected BookRepository $bookRepository
    ) {}

    public function createReview(int $bookId, User $user, array $data): Review
    {
        $book = $this->bookRepository->findById($bookId);

        if (! $book) {
            throw new BookNotFoundException();
        }

        $existing = $this->reviewRepository->findByUserAndBook($user->id, $bookId);

        if ($existing) {
            throw new DuplicateReviewException();
        }

        return DB::transaction(function () use ($book, $user, $data) {
            return $this->reviewRepository->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);
        });
    }

    public function deleteReview(int $bookId, int $reviewId, User $actingUser): bool
    {
        $review = $this->reviewRepository->find($reviewId);

        if (! $review || $review->book_id !== $bookId) {
            throw new ReviewNotFoundException();
        }

        $isOwner = $review->user_id === $actingUser->id;
        $isAdmin = $actingUser->role === Role::ADMIN;

        if (! $isOwner && ! $isAdmin) {
            throw new UnauthorizedActionException('You are not authorized to delete this review.');
        }

        return DB::transaction(function () use ($review) {
            return $this->reviewRepository->delete($review);
        });
    }

    public function getReviewsForBook(int $bookId)
    {
        return $this->reviewRepository->getForBook($bookId);
    }
}