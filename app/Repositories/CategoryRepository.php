<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return Category::withCount('books')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Category
    {
        return Category::with([
            'books' => fn($q) => $q->with('author')->orderBy('title'),
        ])->find($id);
    }

    public function create(array $data): Category
    {
        return Category::create(['name' => $data['name']]);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update(['name' => $data['name']]);

        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }

    public function getBooksForCategory(Category $category, int $perPage = 12): LengthAwarePaginator
    {
        return $category->books()
            ->with('author')
            ->orderBy('title')
            ->paginate($perPage);
    }

    public function nameExistsExcluding(string $name, ?int $excludeId = null): bool
    {
        return Category::where('name', $name)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
