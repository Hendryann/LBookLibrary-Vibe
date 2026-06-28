<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bibliotheca')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">

    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-8">
                    <a href="{{ route('books.index') }}" class="text-xl font-bold text-indigo-600">Bibliotheca</a>
                    <div class="hidden sm:flex items-center gap-6">
                        <a href="{{ route('books.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition">Books</a>
                        <a href="{{ route('authors.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition">Authors</a>
                        <a href="{{ route('categories.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition">Categories</a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                            @if(in_array(auth()->user()->role?->value, ['admin','librarian']))
                                <a href="{{ route('books.create') }}" class="text-xs px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">+ Book</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button class="text-sm text-red-500 hover:underline">Logout</button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:underline">Login</a>
                        <a href="{{ route('register') }}" class="text-sm text-gray-600 hover:underline">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>