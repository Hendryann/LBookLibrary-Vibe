@extends('layouts.app')

@section('title', 'Books — Bibliotheca')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <h1 class="text-2xl font-bold text-gray-900">Books</h1>
    @auth
        @if(in_array(auth()->user()->role?->value, ['admin','librarian']))
            <a href="{{ route('books.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                + Add Book
            </a>
        @endif
    @endauth
</div>

{{-- Search & Filter --}}
<form method="GET" action="{{ route('books.index') }}" class="mb-8 bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Search title, ISBN, author…"
               class="col-span-1 sm:col-span-2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">

        <select name="category" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <select name="sort_by" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="title" @selected(request('sort_by','title') === 'title')>Title</option>
                <option value="publication_year" @selected(request('sort_by') === 'publication_year')>Year</option>
            </select>
            <select name="sort_dir" class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="asc"  @selected(request('sort_dir','asc') === 'asc')>↑ Asc</option>
                <option value="desc" @selected(request('sort_dir') === 'desc')>↓ Desc</option>
            </select>
        </div>
    </div>
    <div class="mt-3 flex gap-2">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">Search</button>
        <a href="{{ route('books.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Reset</a>
    </div>
</form>

{{-- Results --}}
@if($books->isEmpty())
    <div class="text-center py-20 text-gray-400">
        <p class="text-4xl mb-3">📚</p>
        <p class="text-lg font-medium">No books found.</p>
        <p class="text-sm mt-1">Try adjusting your search or filters.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($books as $book)
            <a href="{{ route('books.show', $book->id) }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5 flex flex-col gap-2">
                <h2 class="font-semibold text-gray-900 group-hover:text-indigo-600 line-clamp-2">{{ $book->title }}</h2>
                <p class="text-sm text-gray-500">{{ $book->author->name }}</p>
                @if($book->publication_year)
                    <p class="text-xs text-gray-400">{{ $book->publication_year }}</p>
                @endif
                @if($book->categories->isNotEmpty())
                    <div class="flex flex-wrap gap-1 mt-auto pt-2">
                        @foreach($book->categories->take(3) as $cat)
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs rounded-full">{{ $cat->name }}</span>
                        @endforeach
                    </div>
                @endif
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $books->withQueryString()->links() }}
    </div>
@endif
@endsection