@extends('layouts.app')

@section('title', 'Books in ' . $category->name . ' — Bibliotheca')

@section('content')
<div class="mb-4">
    <a href="{{ route('categories.show', $category->id) }}" class="text-sm text-indigo-600 hover:underline">← Back to {{ $category->name }}</a>
</div>

<h1 class="text-2xl font-bold text-gray-900 mb-6">Books in {{ $category->name }}</h1>

@if($books->isEmpty())
    <div class="text-center py-20 text-gray-400">
        <p>No books in this category.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($books as $book)
            <a href="{{ route('books.show', $book->id) }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5">
                <h2 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $book->title }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $book->author->name }}</p>
            </a>
        @endforeach
    </div>
    <div class="mt-8">{{ $books->links() }}</div>
@endif
@endsection