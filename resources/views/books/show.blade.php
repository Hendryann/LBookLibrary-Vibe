@extends('layouts.app')

@section('title', $book->title . ' — Inventory')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 rounded-lg bg-emerald-900/30 border border-emerald-700 text-emerald-300 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-lg bg-red-900/30 border border-red-700 text-red-300 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Book Info --}}
    <div class="bg-zinc-900 border border-zinc-700 rounded-2xl p-6 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-start gap-6">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-amber-400 mb-1">{{ $book->title }}</h1>
                <p class="text-zinc-400 text-sm mb-1">
                    by <span class="text-zinc-200">{{ $book->author->name ?? '—' }}</span>
                </p>
                <p class="text-zinc-500 text-sm mb-3">ISBN: {{ $book->isbn }}</p>

                @if ($book->categories->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach ($book->categories as $category)
                            <span class="px-2 py-0.5 text-xs rounded-full bg-amber-900/40 text-amber-300 border border-amber-700">
                                {{ $category->name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                @if ($book->description)
                    <p class="text-zinc-400 text-sm leading-relaxed">{{ $book->description }}</p>
                @endif
            </div>

            {{-- Availability Summary --}}
            <div class="shrink-0 w-full sm:w-52">
                <div class="bg-zinc-800 border border-zinc-700 rounded-xl p-4">
                    <h2 class="text-xs font-semibold text-zinc-400 uppercase tracking-widest mb-3">Availability</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-400">Total</span>
                            <span class="text-zinc-200 font-medium">{{ $availability['total'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-emerald-400">Available</span>
                            <span class="text-emerald-300 font-medium">{{ $availability['available'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-400">Borrowed</span>
                            <span class="text-blue-300 font-medium">{{ $availability['borrowed'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-amber-400">Reserved</span>
                            <span class="text-amber-300 font-medium">{{ $availability['reserved'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-red-400">Lost</span>
                            <span class="text-red-300 font-medium">{{ $availability['lost'] }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        @if ($availability['status'] === 'available')
                            <span class="block text-center text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-900/50 text-emerald-300 border border-emerald-700">
                                ✓ Available
                            </span>
                        @elseif ($availability['status'] === 'out_of_stock')
                            <span class="block text-center text-xs font-semibold px-3 py-1.5 rounded-full bg-red-900/50 text-red-300 border border-red-700">
                                ✗ Out of Stock
                            </span>
                        @else
                            <span class="block text-center text-xs font-semibold px-3 py-1.5 rounded-full bg-zinc-700 text-zinc-400 border border-zinc-600">
                                No Copies
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Copy Form (Admin/Librarian) --}}
    @if (auth()->user()?->role === \App\Enums\Role::ADMIN || auth()->user()?->role === \App\Enums\Role::LIBRARIAN)
        <div class="bg-zinc-900 border border-zinc-700 rounded-2xl p-6 mb-8">
            <h2 class="text-lg font-semibold text-zinc-100 mb-4">Add Physical Copy</h2>
            <form method="POST" action="{{ route('books.copies.store', $book) }}" class="flex flex-col sm:flex-row gap-4 items-end">
                @csrf
                <div class="flex-1">
                    <label for="status" class="block text-sm font-medium text-zinc-400 mb-1">Initial Status</label>
                    <select name="status" id="status"
                        class="w-full bg-zinc-800 border border-zinc-600 text-zinc-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        @foreach (\App\Enums\CopyStatus::cases() as $case)
                            <option value="{{ $case->value }}" {{ old('status') === $case->value ? 'selected' : '' }}>
                                {{ $case->value }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-900 font-semibold text-sm rounded-lg transition-colors">
                    Add Copy
                </button>
            </form>
        </div>
    @endif

    {{-- Copies List --}}
    <div class="bg-zinc-900 border border-zinc-700 rounded-2xl p-6">
        <h2 class="text-lg font-semibold text-zinc-100 mb-4">Physical Copies
            <span class="text-sm text-zinc-500 font-normal">({{ $copies->count() }})</span>
        </h2>

        @if ($copies->isEmpty())
            <div class="py-12 text-center">
                <p class="text-zinc-500 text-sm">No physical copies registered for this book.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-zinc-700">
                            <th class="pb-3 pr-4 text-xs font-semibold text-zinc-400 uppercase tracking-wider">Barcode</th>
                            <th class="pb-3 pr-4 text-xs font-semibold text-zinc-400 uppercase tracking-wider">Status</th>
                            <th class="pb-3 pr-4 text-xs font-semibold text-zinc-400 uppercase tracking-wider">Added</th>
                            @if (auth()->user()?->role === \App\Enums\Role::ADMIN || auth()->user()?->role === \App\Enums\Role::LIBRARIAN)
                                <th class="pb-3 text-xs font-semibold text-zinc-400 uppercase tracking-wider text-right">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @foreach ($copies as $copy)
                            <tr class="group">
                                <td class="py-3 pr-4 font-mono text-zinc-300">{{ $copy->barcode }}</td>
                                <td class="py-3 pr-4">
                                    @php
                                        $statusClasses = match($copy->status) {
                                            \App\Enums\CopyStatus::AVAILABLE => 'bg-emerald-900/40 text-emerald-300 border-emerald-700',
                                            \App\Enums\CopyStatus::BORROWED  => 'bg-blue-900/40 text-blue-300 border-blue-700',
                                            \App\Enums\CopyStatus::RESERVED  => 'bg-amber-900/40 text-amber-300 border-amber-700',
                                            \App\Enums\CopyStatus::LOST      => 'bg-red-900/40 text-red-300 border-red-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs rounded-full border {{ $statusClasses }}">
                                        {{ $copy->status->value }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-zinc-500">{{ $copy->created_at->format('d M Y') }}</td>

                                @if (auth()->user()?->role === \App\Enums\Role::ADMIN || auth()->user()?->role === \App\Enums\Role::LIBRARIAN)
                                    <td class="py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Update Status Form --}}
                                            <form method="POST" action="{{ route('books.copies.update', [$book, $copy->id]) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <select name="status"
                                                    class="bg-zinc-800 border border-zinc-600 text-zinc-200 rounded text-xs px-2 py-1 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                                    @foreach (\App\Enums\CopyStatus::cases() as $case)
                                                        <option value="{{ $case->value }}" {{ $copy->status === $case ? 'selected' : '' }}>
                                                            {{ $case->value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit"
                                                    class="text-xs px-3 py-1 bg-zinc-700 hover:bg-zinc-600 text-zinc-200 rounded transition-colors">
                                                    Update
                                                </button>
                                            </form>

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('books.copies.destroy', [$book, $copy->id]) }}"
                                                onsubmit="return confirm('Delete copy {{ $copy->barcode }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-xs px-3 py-1 bg-red-900/50 hover:bg-red-800/70 text-red-300 rounded transition-colors border border-red-800">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">
        <a href="{{ route('books.index') }}" class="text-sm text-zinc-400 hover:text-amber-400 transition-colors">
            ← Back to Catalog
        </a>
    </div>
</div>
@endsection