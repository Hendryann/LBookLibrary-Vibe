<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\StoreReviewRequest;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService
    ) {}

    public function index(int $bookId)
    {
        $reviews = $this->reviewService->getReviewsForBook($bookId);

        return view('reviews.index', compact('reviews', 'bookId'));
    }

    public function store(StoreReviewRequest $request, int $bookId)
    {
        $this->reviewService->createReview($bookId, Auth::user(), $request->validated());

        return redirect()
            ->route('books.show', $bookId)
            ->with('success', 'Review submitted successfully.');
    }

    public function destroy(Request $request, int $bookId, int $reviewId)
    {
        $this->reviewService->deleteReview($bookId, $reviewId, Auth::user());

        return redirect()
            ->route('books.show', $bookId)
            ->with('success', 'Review deleted successfully.');
    }
}