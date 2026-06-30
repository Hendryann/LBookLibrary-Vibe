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
        <p class="text-4xl mb-3">📁</p>
        <p class="text-lg font-medium">No categories yet.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($categories as $category)
            <a href="{{ route('categories.show', $category->id) }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5">
                <h2 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $category->name }}</h2>
                <p class="text-sm text-gray-400 mt-1">{{ $category->books_count ?? $category->books->count() }} {{ Str::plural('book', $category->books_count ?? $category->books->count()) }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-8">{{ $categories->links() }}</div>
@endif
@endsection
