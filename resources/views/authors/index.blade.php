@extends('layouts.app')

@section('title', 'Authors — Bibliotheca')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Authors</h1>
    @auth
        @if(in_array(auth()->user()->role?->value, ['admin','librarian']))
            <a href="{{ route('authors.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                + Add Author
            </a>
        @endif
    @endauth
</div>

@if($authors->isEmpty())
    <div class="text-center py-20 text-gray-400">
        <p class="text-4xl mb-3">✍️</p>
        <p class="text-lg font-medium">No authors yet.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($authors as $author)
            <a href="{{ route('authors.show', $author->id) }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5">
                <h2 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $author->name }}</h2>
                <p class="text-sm text-gray-400 mt-1">{{ $author->books_count }} {{ Str::plural('book', $author->books_count) }}</p>
                @if($author->biography)
                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $author->biography }}</p>
                @endif
            </a>
        @endforeach
    </div>

    <div class="mt-8">{{ $authors->links() }}</div>
@endif
@endsection