@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-2xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-semibold text-zinc-100 mb-6">Profile</h1>

    @if (session('success'))
        <div class="mb-4 rounded-md bg-emerald-900/40 border border-emerald-700 text-emerald-300 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-900/40 border border-red-700 text-red-300 px-4 py-3">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.update', $user->id) }}" class="bg-zinc-900 border border-zinc-800 rounded-lg p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm text-zinc-400 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                   class="w-full rounded-md bg-zinc-800 border border-zinc-700 text-zinc-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500">
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                   class="w-full rounded-md bg-zinc-800 border border-zinc-700 text-zinc-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500">
        </div>

        <div>
            <label class="block text-sm text-zinc-400 mb-1">Role</label>
            <p class="text-zinc-300">{{ $user->role->value }}</p>
        </div>

        <button type="submit"
                class="bg-amber-600 hover:bg-amber-500 text-zinc-950 font-medium px-4 py-2 rounded-md transition">
            Save Changes
        </button>
    </form>

    <div class="mt-6 flex gap-4">
        <a href="{{ route('users.history') }}" class="text-amber-400 hover:underline">View Borrowing History</a>
        <a href="{{ route('users.recommendations') }}" class="text-amber-400 hover:underline">View Recommendations</a>
    </div>
</div>
@endsection