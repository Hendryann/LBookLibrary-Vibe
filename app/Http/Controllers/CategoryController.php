<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Enums\Role;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service) {}

    public function index(): View
    {
        $categories = $this->service->list();

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category): View
    {
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

    public function edit(Category $category): View
    {
        $this->authorizeManage();

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->service->update($category, $request->validated());

        return redirect()->route('categories.show', $category->id)
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeManage();

        $result = $this->service->delete($category);

        return redirect()->route('categories.index')
            ->with('success', $result['message']);
    }

    public function books(Category $category): View
    {
        $books = $this->service->categoryBooks($category);

        return view('categories.books', compact('category', 'books'));
    }

    private function authorizeManage(): void
    {
        $user = auth()->user();
        if (! $user || ! in_array($user->role, [Role::ADMIN, Role::LIBRARIAN], true)) {
            abort(403, 'Unauthorized.');
        }
    }
}
