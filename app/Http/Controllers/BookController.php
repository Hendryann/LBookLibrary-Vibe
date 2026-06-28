<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Services\BookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function __construct(private readonly BookService $service) {}

    public function index(Request $request): View
    {
        $books      = $this->service->search($request->only(['q', 'category', 'sort_by', 'sort_dir']));
        $categories = Category::orderBy('name')->get();

        return view('books.index', compact('books', 'categories'));
    }

    public function show(Book $book): View
    {
        return view('books.show', compact('book'));
    }

    public function create(): View
    {
        $this->authorizeManage();
        $authors    = Author::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('books.create', compact('authors', 'categories'));
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = $this->service->create($request->validated());

        return redirect()->route('books.show', $book->id)
            ->with('success', 'Book created successfully.');
    }

    public function edit(Book $book): View
    {
        $this->authorizeManage();

        $authors    = Author::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('books.edit', compact('book', 'authors', 'categories'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->service->update($book, $request->validated());

        return redirect()->route('books.show', $book->id)
            ->with('success', 'Book updated successfully.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorizeManage();
        $this->service->delete($book);

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully.');
    }

    private function authorizeManage(): void
    {
        $role = auth()->user()?->role?->value ?? '';

        if (!in_array($role, ['admin', 'librarian'], true)) {
            abort(403, 'Unauthorized.');
        }
    }
}
