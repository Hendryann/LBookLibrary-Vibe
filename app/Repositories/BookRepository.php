<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Pagination\LengthAwarePaginator;

class BookRepository
{
    public function search(
        ?string $query    = null,
        ?int    $category = null,
        string  $sortBy   = 'title',
        string  $sortDir  = 'asc',
        int     $perPage  = 12
    ): LengthAwarePaginator {
        $q = Book::with(['author', 'categories']);

        if ($query) {
            $q->where(function ($sub) use ($query) {
                $sub->where('title', 'like', "%{$query}%")
                    ->orWhere('isbn', 'like', "%{$query}%")
                    ->orWhereHas('author', fn($a) => $a->where('name', 'like', "%{$query}%"));
            });
        }

        if ($category) {
            $q->whereHas('categories', fn($c) => $c->where('categories.id', $category));
        }

        $allowedSorts = ['title', 'publication_year'];
        $sortColumn   = in_array($sortBy, $allowedSorts, true) ? $sortBy : 'title';
        $sortDir      = in_array(strtolower($sortDir), ['asc', 'desc']) ? strtolower($sortDir) : 'asc';

        $q->orderBy($sortColumn, $sortDir);

        return $q->paginate($perPage)->withQueryString();
    }

    public function findById(int $id): ?Book
    {
        return Book::with(['author', 'categories', 'reviews'])->find($id);
    }

    public function create(array $data): Book
    {
        $book = Book::create([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'isbn'             => $data['isbn'] ?? null,
            'publication_year' => $data['publication_year'] ?? null,
            'author_id'        => $data['author_id'],
        ]);

        if (!empty($data['category_ids'])) {
            $book->categories()->sync($data['category_ids']);
        }

        return $book->load(['author', 'categories']);
    }

    public function update(Book $book, array $data): Book
    {
        $book->update([
            'title'            => $data['title'] ?? $book->title,
            'description'      => $data['description'] ?? $book->description,
            'isbn'             => $data['isbn'] ?? $book->isbn,
            'publication_year' => $data['publication_year'] ?? $book->publication_year,
            'author_id'        => $data['author_id'] ?? $book->author_id,
        ]);

        $book->categories()->sync($data['category_ids'] ?? []);

        return $book->fresh(['author', 'categories']);
    }

    public function delete(Book $book): bool
    {
        return (bool) $book->delete();
    }
}
