<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(private readonly CategoryRepository $repo) {}

    public function list(): LengthAwarePaginator
    {
        return $this->repo->paginate();
    }

    public function find(int $id): ?Category
    {
        return $this->repo->findById($id);
    }

    public function create(array $data): Category
    {
        return $this->repo->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        return $this->repo->update($category, $data);
    }

    public function delete(Category $category): array
    {
        $category->books()->detach();
        $this->repo->delete($category);

        return ['success' => true, 'message' => 'Category deleted successfully.'];
    }

    public function categoryBooks(Category $category): LengthAwarePaginator
    {
        return $this->repo->getBooksForCategory($category);
    }
}
