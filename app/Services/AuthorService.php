<?php

namespace App\Services;

use App\Models\Author;
use App\Repositories\AuthorRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorService
{
    public function __construct(private readonly AuthorRepository $repo) {}

    public function list(): LengthAwarePaginator
    {
        return $this->repo->paginate();
    }

    public function find(int $id): ?Author
    {
        return $this->repo->findById($id);
    }

    public function create(array $data): Author
    {
        return $this->repo->create($data);
    }

    public function update(Author $author, array $data): Author
    {
        return $this->repo->update($author, $data);
    }

    public function delete(Author $author): array
    {
        if ($author->books()->exists()) {
            return ['success' => false, 'message' => 'Cannot delete an author who has associated books.'];
        }

        $this->repo->delete($author);

        return ['success' => true, 'message' => 'Author deleted successfully.'];
    }

    public function authorBooks(Author $author): LengthAwarePaginator
    {
        return $this->repo->getBooksForAuthor($author);
    }
}
