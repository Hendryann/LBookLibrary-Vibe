@extends('layouts.app')

@section('title', 'Books by ' . $author->name . ' — Bibliotheca')

@section('content')
<div class="mb-4">
    <a href="{{ route('authors.show', $author->id) }}" class="text-sm text-indigo-600 hover:underline">← Back to {{ $author->name }}</a>
</div>

<h1 class="text-2xl font-bold text-gray-900 mb-6">Books by {{ $author->name }}</h1>

@if($books->isEmpty())
    <div class="text-center py-20 text-gray-400">
        <p>No books by this author.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($books as $book)
            <a href="{{ route('books.show', $book->id) }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5">
                <h2 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $book->title }}</h2>
                @if($book->publication_year)
                    <p class="text-xs text-gray-400 mt-1">{{ $book->publication_year }}</p>
                @endif
                @if($book->categories->isNotEmpty())
                    <div class="flex flex-wrap gap-1 mt-2">
                        @foreach($book->categories->take(3) as $cat)
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs rounded-full">{{ $cat->name }}</span>
                        @endforeach
                    </div>
                @endif
            </a>
        @endforeach
    </div>
    <div class="mt-8">{{ $books->links() }}</div>
@endif
@endsection
```

---

## `resources/views/categories/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Categories — Bibliotheca')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
    @auth
        @if(in_array(auth()->user()->role?->value, ['admin','librarian']))
            <a href="{{ route('categories.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                + Add Category
            </a>
        @endif
    @endauth
</div>

@if($categories->isEmpty())
    <div class="text-center py-20 text-gray-400">
        <p class="text-4xl mb-3">🏷️</p>
        <p class="text-lg font-medium">No categories yet.</p>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($categories as $cat)
            <a href="{{ route('categories.show', $cat->id) }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5 text-center">
                <h2 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $cat->name }}</h2>
                <p class="text-sm text-gray-400 mt-1">{{ $cat->books_count }} {{ Str::plural('book', $cat->books_count) }}</p>
            </a>
        @endforeach
    </div>
    <div class="mt-8">{{ $categories->links() }}</div>
@endif
@endsection