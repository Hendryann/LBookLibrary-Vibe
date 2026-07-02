<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Repositories\BookRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function __construct(
        protected ReviewRepository $reviewRepository,
        protected BookRepository $bookRepository
    ) {}

    public function createReview(int $bookId, User $user, array $data): Review
    {
        $book = $this->bookRepository->find($bookId);

        if (! $book) {
            throw ValidationException::withMessages([
                'book' => 'Book not found.',
            ])->status(404);
        }

        $existing = $this->reviewRepository->findByUserAndBook($user->id, $bookId);

        if ($existing) {
            throw ValidationException::withMessages([
                'review' => 'You have already reviewed this book.',
            ])->status(409);
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
            throw ValidationException::withMessages([
                'review' => 'Review not found.',
            ])->status(404);
        }

        $isOwner = $review->user_id === $actingUser->id;
        $isAdmin = $actingUser->role === Role::ADMIN;

        if (! $isOwner && ! $isAdmin) {
            throw ValidationException::withMessages([
                'authorization' => 'You are not authorized to delete this review.',
            ])->status(403);
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