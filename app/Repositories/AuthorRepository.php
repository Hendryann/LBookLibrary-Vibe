<?php

namespace App\Repositories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorRepository
{
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return Author::withCount('books')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Author
    {
        return Author::with([
            'books' => fn($q) => $q->with('categories')->orderBy('title'),
        ])->find($id);
    }

    public function create(array $data): Author
    {
        return Author::create([
            'name'      => $data['name'],
            'biography' => $data['biography'] ?? null,
        ]);
    }

    public function update(Author $author, array $data): Author
    {
        $author->update([
            'name'      => $data['name'],
            'biography' => $data['biography'] ?? null,
        ]);

        return $author->fresh();
    }

    public function delete(Author $author): bool
    {
        return (bool) $author->delete();
    }

    public function getBooksForAuthor(Author $author, int $perPage = 12): LengthAwarePaginator
    {
        return $author->books()
            ->with('categories')
            ->orderBy('title')
            ->paginate($perPage);
    }

    public function existsById(int $id): bool
    {
        return Author::where('id', $id)->exists();
    }
}
