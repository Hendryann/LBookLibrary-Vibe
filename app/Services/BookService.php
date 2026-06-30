<?php

namespace App\Services;

use App\Models\Book;
use App\Repositories\BookRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class BookService
{
    public function __construct(private readonly BookRepository $repo) {}

    public function search(array $filters): LengthAwarePaginator
    {
        return $this->repo->search(
            query: $filters['q']       ?? null,
            category: $filters['category'] ?? null,
            sortBy: $filters['sort_by']  ?? 'title',
            sortDir: $filters['sort_dir'] ?? 'asc',
        );
    }

    public function find(int $id): ?Book
    {
        return $this->repo->findById($id);
    }

    public function create(array $data): Book
    {
        return $this->repo->create($data);
    }

    public function update(Book $book, array $data): Book
    {
        return $this->repo->update($book, $data);
    }

    public function delete(Book $book): array
    {
        $this->repo->delete($book);

        return ['success' => true, 'message' => 'Book deleted successfully.'];
    }
}
