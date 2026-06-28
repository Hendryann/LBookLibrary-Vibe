@extends('layouts.app')

@section('title', $author->name . ' — Bibliotheca')

@section('content')
<div class="mb-4">
    <a href="{{ route('authors.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to Authors</a>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 mb-8">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $author->name }}</h1>
            <p class="text-sm text-gray-400 mt-1">{{ $author->books->count() }} {{ Str::plural('book', $author->books->count()) }}</p>
        </div>
        @auth
            @if(in_array(auth()->user()->role?->value, ['admin','librarian']))
                <div class="flex gap-2 shrink-0">
                    <a href="{{ route('authors.edit', $author->id) }}"
                       class="px-4 py-2 bg-amber-500 text-white text-sm rounded-lg hover:bg-amber-600">Edit</a>
                    <form method="POST" action="{{ route('authors.destroy', $author->id) }}"
                          onsubmit="return confirm('Delete this author?')">
                        @csrf @method('DELETE')
                        <button class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">Delete</button>
                    </form>
                </div>
            @endif
        @endauth
    </div>

    @if($author->biography)
        <div class="mt-6">
            <p class="text-sm font-medium text-gray-400 mb-2">Biography</p>
            <p class="text-gray-700 leading-relaxed">{{ $author->biography }}</p>
        </div>
    @endif
</div>

<h2 class="text-xl font-bold text-gray-900 mb-4">Books by {{ $author->name }}</h2>

@if($author->books->isEmpty())
    <div class="text-center py-12 text-gray-400">
        <p>No books by this author yet.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($author->books as $book)
            <a href="{{ route('books.show', $book->id) }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5">
                <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $book->title }}</h3>
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
@endif
@endsection