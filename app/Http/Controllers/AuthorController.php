<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Models\Author;
use App\Services\AuthorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Enums\Role;

class AuthorController extends Controller
{
    public function __construct(private readonly AuthorService $service) {}

    public function index(): View
    {
        $authors = $this->service->list();

        return view('authors.index', compact('authors'));
    }

    public function show(Author $author): View
    {
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

    public function edit(Author $author): View
    {
        $this->authorizeManage();

        return view('authors.edit', compact('author'));
    }

    public function update(UpdateAuthorRequest $request, Author $author): RedirectResponse
    {
        $this->service->update($author, $request->validated());

        return redirect()->route('authors.show', $author->id)
            ->with('success', 'Author updated successfully.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        $this->authorizeManage();

        $result = $this->service->delete($author);

        if (!$result['success']) {
            return redirect()->route('authors.show', $author->id)
                ->with('error', $result['message']);
        }

        return redirect()->route('authors.index')
            ->with('success', $result['message']);
    }

    public function books(Author $author): View
    {
        $books = $this->service->authorBooks($author);

        return view('authors.books', compact('author', 'books'));
    }

    private function authorizeManage(): void
    {
        $user = auth()->user();
        if (! $user || ! in_array($user->role, [Role::ADMIN, Role::LIBRARIAN], true)) {
            abort(403, 'Unauthorized.');
        }
    }
}