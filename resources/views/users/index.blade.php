@extends('layouts.app')

@section('title', 'All Users')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-semibold text-zinc-100 mb-6">All Users</h1>

    @if (session('success'))
        <div class="mb-4 rounded-md bg-emerald-900/40 border border-emerald-700 text-emerald-300 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if ($users->isEmpty())
        <p class="text-zinc-400">No users found.</p>
    @else
        <div class="overflow-x-auto rounded-lg border border-zinc-800">
            <table class="min-w-full divide-y divide-zinc-800">
                <thead class="bg-zinc-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase">Role</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-4 py-3 text-zinc-200">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $user->role->value }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('users.show', $user->id) }}" class="text-amber-400 hover:underline mr-3">View</a>
                                <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection