<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service) {}

    public function index(): View
    {
        $categories = $this->service->list();

        return view('categories.index', compact('categories'));
    }

    public function show(int $id): View
    {
        $category = $this->service->find($id);

        if (!$category) {
            abort(404, 'Category not found.');
        }

        return view('categories.show', compact('category'));
    }

    public function create(): View
    {
        $this->authorizeManage();

        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = $this->service->create($request->validated());

        return redirect()->route('categories.show', $category->id)
            ->with('success', 'Category created successfully.');
    }

    public function edit(int $id): View
    {
        $this->authorizeManage();
        $category = $this->service->find($id);

        if (!$category) {
            abort(404, 'Category not found.');
        }

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, int $id): RedirectResponse
    {
        $category = $this->service->find($id);

        if (!$category) {
            abort(404, 'Category not found.');
        }

        $this->service->update($category, $request->validated());

        return redirect()->route('categories.show', $category->id)
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeManage();
        $category = $this->service->find($id);

        if (!$category) {
            abort(404, 'Category not found.');
        }

        $result = $this->service->delete($category);

        return redirect()->route('categories.index')
            ->with('success', $result['message']);
    }

    public function books(int $id): View
    {
        $category = $this->service->find($id);

        if (!$category) {
            abort(404, 'Category not found.');
        }

        $books = $this->service->categoryBooks($category);

        return view('categories.books', compact('category', 'books'));
    }

    private function authorizeManage(): void
    {
        $role = auth()->user()?->role?->value ?? '';

        if (!in_array($role, ['admin', 'librarian'], true)) {
            abort(403, 'Unauthorized.');
        }
    }
}
