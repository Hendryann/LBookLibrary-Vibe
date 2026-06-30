@extends('layouts.app')

@section('title', 'Dashboard — Bibliotheca')

@section('content')
<div class="max-w-md mx-auto text-center space-y-4 py-12">
    <div class="w-12 h-12 bg-amber-500 rounded-sm flex items-center justify-center mx-auto shadow-sm">
        <svg class="w-6 h-6 text-zinc-950" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M6 4h12v2H6zm0 14h12v2H6zM9 8h2v8H9zm4 0h2v8h-2z"/>
        </svg>
    </div>
    
    <h1 class="font-serif text-2xl text-gray-900 font-bold">
        Welcome, {{ auth()->user()->name }}
    </h1>
    
    <p class="text-gray-600 text-sm">
        Authenticated as <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-xs rounded font-semibold uppercase tracking-wider">{{ auth()->user()->role->value }}</span>
    </p>

    <div class="pt-6">
        <a href="{{ route('books.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            Browse Books
        </a>
    </div>
</div>
@endsection