<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Bibliotheca</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 antialiased flex items-center justify-center">
    <div class="text-center space-y-4">
        <div class="w-12 h-12 bg-amber-500 rounded-sm flex items-center justify-center mx-auto">
            <svg class="w-6 h-6 text-zinc-950" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 4h12v2H6zm0 14h12v2H6zM9 8h2v8H9zm4 0h2v8h-2z"/>
            </svg>
        </div>
        <h1 class="text-white font-serif text-2xl">
            Welcome, {{ auth()->user()->name }}
        </h1>
        <p class="text-zinc-500 text-sm">
            Authenticated as <span class="text-amber-400">{{ auth()->user()->role->value }}</span>
        </p>
        <form action="{{ route('auth.logout') }}" method="POST" class="inline-block pt-4">
            @csrf
            <button
                type="submit"
                class="px-5 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-sm rounded-lg
                       transition-colors duration-150 border border-zinc-700"
            >
                Sign out
            </button>
        </form>
    </div>
</body>
</html>