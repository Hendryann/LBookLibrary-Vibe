@extends('layouts.app')

@section('title', $book->title . ' — Bibliotheca')

@section('content')
<div class="mb-4">
    <a href="{{ route('books.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to Books</a>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $book->title }}</h1>
            <p class="mt-2 text-gray-500">
                by <a href="{{ route('authors.show', $book->author->id) }}" class="text-indigo-600 hover:underline">{{ $book->author->name }}</a>
            </p>
        </div>

        @auth
            @if(in_array(auth()->user()->role?->value, ['admin','librarian']))
                <div class="flex gap-2 shrink-0">
                    <a href="{{ route('books.edit', $book->id) }}"
                       class="px-4 py-2 bg-amber-500 text-white text-sm rounded-lg hover:bg-amber-600">Edit</a>
                    <form method="POST" action="{{ route('books.destroy', $book->id) }}"
                          onsubmit="return confirm('Delete this book?')">
                        @csrf @method('DELETE')
                        <button class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">Delete</button>
                    </form>
                </div>
            @endif
        @endauth
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-sm">
        @if($book->isbn)
            <div>
                <dt class="text-gray-400 font-medium">ISBN</dt>
                <dd class="text-gray-800">{{ $book->isbn }}</dd>
            </div>
        @endif
        @if($book->publication_year)
            <div>
                <dt class="text-gray-400 font-medium">Publication Year</dt>
                <dd class="text-gray-800">{{ $book->publication_year }}</dd>
            </div>
        @endif
    </dl>

    @if($book->categories->isNotEmpty())
        <div class="mb-6">
            <p class="text-sm font-medium text-gray-400 mb-2">Categories</p>
            <div class="flex flex-wrap gap-2">
                @foreach($book->categories as $cat)
                    <a href="{{ route('categories.show', $cat->id) }}"
                       class="px-3 py-1 bg-indigo-50 text-indigo-700 text-sm rounded-full hover:bg-indigo-100">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($book->description)
        <div>
            <p class="text-sm font-medium text-gray-400 mb-2">Description</p>
            <p class="text-gray-700 leading-relaxed">{{ $book->description }}</p>
        </div>
    @endif
</div>
@endsection