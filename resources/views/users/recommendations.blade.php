@extends('layouts.app')

@section('title', 'Recommended For You')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-semibold text-zinc-100 mb-6">Recommended For You</h1>

    @if ($recommendations->isEmpty())
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6 text-zinc-400">
            No recommendations available yet. Borrow a few books to get personalized suggestions.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($recommendations as $book)
                <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                    <h3 class="text-zinc-100 font-medium">{{ $book->title }}</h3>
                    <p class="text-zinc-500 text-sm mb-2">{{ $book->author->name ?? 'Unknown Author' }}</p>
                    <div class="flex flex-wrap gap-1 mb-2">
                        @foreach ($book->categories as $category)
                            <span class="text-xs bg-zinc-800 text-amber-400 px-2 py-0.5 rounded">{{ $category->name }}</span>
                        @endforeach
                    </div>
                    <p class="text-xs text-zinc-600">Match score: {{ $book->recommendation_score }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection