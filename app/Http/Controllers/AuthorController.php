<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Services\AuthorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function __construct(private readonly AuthorService $service) {}

    public function index(): View
    {
        $authors = $this->service->list();

        return view('authors.index', compact('authors'));
    }

    public function show(int $id): View
    {
        $author = $this->service->find($id);

        if (!$author) {
            abort(404, 'Author not found.');
        }

        return view('authors.show', compact('author'));
    }

    public function create(): View
    {
        $this->authorizeManage();

        return view('authors.create');
    }

    public function store(StoreAuthorRequest $request): RedirectResponse
    {
        $author = $this->service->create($request->validated());

        return redirect()->route('authors.show', $author->id)
            ->with('success', 'Author created successfully.');
    }

    public function edit(int $id): View
    {
        $this->authorizeManage();
        $author = $this->service->find($id);

        if (!$author) {
            abort(404, 'Author not found.');
        }

        return view('authors.edit', compact('author'));
    }

    public function update(UpdateAuthorRequest $request, int $id): RedirectResponse
    {
        $author = $this->service->find($id);

        if (!$author) {
            abort(404, 'Author not found.');
        }

        $this->service->update($author, $request->validated());

        return redirect()->route('authors.show', $author->id)
            ->with('success', 'Author updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeManage();
        $author = $this->service->find($id);

        if (!$author) {
            abort(404, 'Author not found.');
        }

        $result = $this->service->delete($author);

        if (!$result['success']) {
            return redirect()->route('authors.show', $author->id)
                ->with('error', $result['message']);
        }

        return redirect()->route('authors.index')
            ->with('success', $result['message']);
    }

    public function books(int $id): View
    {
        $author = $this->service->find($id);

        if (!$author) {
            abort(404, 'Author not found.');
        }

        $books = $this->service->authorBooks($author);

        return view('authors.books', compact('author', 'books'));
    }

    private function authorizeManage(): void
    {
        $role = auth()->user()?->role?->value ?? '';

        if (!in_array($role, ['admin', 'librarian'], true)) {
            abort(403, 'Unauthorized.');
        }
    }
}
