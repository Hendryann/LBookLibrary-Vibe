@extends('layouts.app')

@section('title', $category->name . ' — Bibliotheca')

@section('content')
<div class="mb-4">
    <a href="{{ route('categories.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to Categories</a>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 mb-8">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
            <p class="text-sm text-gray-400 mt-1">{{ $category->books->count() }} {{ Str::plural('book', $category->books->count()) }}</p>
        </div>
        @auth
            @if(in_array(auth()->user()->role?->value, ['admin','librarian']))
                <div class="flex gap-2 shrink-0">
                    <a href="{{ route('categories.edit', $category->id) }}"
                       class="px-4 py-2 bg-amber-500 text-white text-sm rounded-lg hover:bg-amber-600">Edit</a>
                    <form method="POST" action="{{ route('categories.destroy', $category->id) }}"
                          onsubmit="return confirm('Delete this category?')">
                        @csrf @method('DELETE')
                        <button class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">Delete</button>
                    </form>
                </div>
            @endif
        @endauth
    </div>
</div>

<h2 class="text-xl font-bold text-gray-900 mb-4">Books in {{ $category->name }}</h2>

@if($category->books->isEmpty())
    <div class="text-center py-12 text-gray-400">
        <p>No books in this category yet.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($category->books as $book)
            <a href="{{ route('books.show', $book->id) }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5">
                <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $book->title }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $book->author->name }}</p>
                @if($book->publication_year)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $book->publication_year }}</p>
                @endif
            </a>
        @endforeach
    </div>
@endif
@endsection