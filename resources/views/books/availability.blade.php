@extends('layouts.app')

@section('title', 'Availability — ' . $book->title)

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-zinc-900 border border-zinc-700 rounded-2xl p-6">
        <h1 class="text-xl font-bold text-amber-400 mb-1">{{ $book->title }}</h1>
        <p class="text-zinc-500 text-sm mb-6">by {{ $book->author->name ?? '—' }}</p>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div class="bg-zinc-800 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-zinc-100">{{ $availability['total'] }}</div>
                <div class="text-xs text-zinc-400 mt-1">Total Copies</div>
            </div>
            <div class="bg-emerald-900/30 border border-emerald-800 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-emerald-300">{{ $availability['available'] }}</div>
                <div class="text-xs text-emerald-500 mt-1">Available</div>
            </div>
            <div class="bg-blue-900/30 border border-blue-800 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-blue-300">{{ $availability['borrowed'] }}</div>
                <div class="text-xs text-blue-500 mt-1">Borrowed</div>
            </div>
            <div class="bg-amber-900/30 border border-amber-800 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-amber-300">{{ $availability['reserved'] }}</div>
                <div class="text-xs text-amber-500 mt-1">Reserved</div>
            </div>
            <div class="bg-red-900/30 border border-red-800 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-red-300">{{ $availability['lost'] }}</div>
                <div class="text-xs text-red-500 mt-1">Lost</div>
            </div>
        </div>

        <div class="mt-6 text-center">
            @if ($availability['status'] === 'available')
                <span class="inline-block px-4 py-2 rounded-full bg-emerald-900/50 text-emerald-300 border border-emerald-700 text-sm font-semibold">
                    ✓ Available for Borrowing
                </span>
            @elseif ($availability['status'] === 'out_of_stock')
                <span class="inline-block px-4 py-2 rounded-full bg-red-900/50 text-red-300 border border-red-700 text-sm font-semibold">
                    ✗ Currently Out of Stock
                </span>
            @else
                <span class="inline-block px-4 py-2 rounded-full bg-zinc-700 text-zinc-400 border border-zinc-600 text-sm font-semibold">
                    No Copies Registered
                </span>
            @endif
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('books.copies.index', $book) }}" class="text-sm text-amber-400 hover:text-amber-300">
                View all copies →
            </a>
        </div>
    </div>
</div>
@endsection