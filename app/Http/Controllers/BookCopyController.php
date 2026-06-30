<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreBookCopyRequest;
use App\Http\Requests\UpdateBookCopyRequest;
use App\Models\Book;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class BookCopyController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {}

    /**
     * Authorize manage-inventory for store, update, destroy only.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(function (Request $request, \Closure $next) {
                $user = $request->user();

                if (
                    self::isManagementAction($request)
                    && (! $user || ! in_array($user->role, [Role::ADMIN, Role::LIBRARIAN], true))
                ) {
                    abort(403, 'Unauthorized to manage inventory.');
                }

                return $next($request);
            }),
        ];
    }

    private static function isManagementAction(Request $request): bool
    {
        return in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'], true);
    }

    /**
     * Display all copies of a book (Book Detail page).
     */
    public function index(Book $book): View
    {
        $copies = $this->inventoryService->getCopiesForBook($book->id);
        $availability = $this->inventoryService->getAvailability($book->id);
        $book->load(['author', 'categories']);

        return view('books.show', compact('book', 'copies', 'availability'));
    }

    /**
     * Store a new book copy.
     */
    public function store(StoreBookCopyRequest $request, Book $book): RedirectResponse
    {
        $this->inventoryService->createCopy($book, $request->validated());

        return redirect()
            ->route('books.copies.index', $book)
            ->with('success', 'Book copy added successfully.');
    }

    /**
     * Update an existing book copy status.
     */
    public function update(UpdateBookCopyRequest $request, Book $book, int $copyId): RedirectResponse
    {
        $copy = $this->inventoryService
            ->getCopiesForBook($book->id)
            ->firstWhere('id', $copyId);

        if (! $copy) {
            abort(404, 'Book copy not found.');
        }

        $newStatus = \App\Enums\CopyStatus::from($request->validated()['status']);

        if (! $this->inventoryService->isValidStatusTransition($copy->status, $newStatus)) {
            return redirect()
                ->route('books.copies.index', $book)
                ->with('error', "Cannot change status from {$copy->status->value} to {$newStatus->value}.");
        }

        $this->inventoryService->updateCopy($copy, $request->validated());

        return redirect()
            ->route('books.copies.index', $book)
            ->with('success', 'Book copy status updated successfully.');
    }

    /**
     * Delete a book copy.
     */
    public function destroy(Book $book, int $copyId): RedirectResponse
    {
        $copy = $this->inventoryService
            ->getCopiesForBook($book->id)
            ->firstWhere('id', $copyId);

        if (! $copy) {
            abort(404, 'Book copy not found.');
        }

        $this->inventoryService->deleteCopy($copy);

        return redirect()
            ->route('books.copies.index', $book)
            ->with('success', 'Book copy deleted successfully.');
    }

    /**
     * Get availability info for a book (can also be used as a JSON endpoint).
     */
    public function availability(Book $book): View
    {
        $availability = $this->inventoryService->getAvailability($book->id);
        $book->load(['author', 'categories']);

        return view('books.availability', compact('book', 'availability'));
    }
}