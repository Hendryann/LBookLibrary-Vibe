@extends('layouts.app')

@section('title', 'Add Book — Bibliotheca')

@section('content')
<div class="mb-4">
    <a href="{{ route('books.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to Books</a>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Book</h1>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('books.store') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('title') border-red-400 @enderror">
            @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Author <span class="text-red-500">*</span></label>
            <select name="author_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('author_id') border-red-400 @enderror">
                <option value="">Select author…</option>
                @foreach($authors as $author)
                    <option value="{{ $author->id }}" @selected(old('author_id') == $author->id)>{{ $author->name }}</option>
                @endforeach
            </select>
            @error('author_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ISBN</label>
            <input type="text" name="isbn" value="{{ old('isbn') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('isbn') border-red-400 @enderror">
            @error('isbn')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Publication Year</label>
            <input type="number" name="publication_year" value="{{ old('publication_year') }}"
                   min="1000" max="{{ date('Y') + 1 }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('publication_year') border-red-400 @enderror">
            @error('publication_year')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
            <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                @foreach($categories as $cat)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                               @checked(in_array($cat->id, old('category_ids', [])))
                               class="rounded border-gray-300 text-indigo-600">
                        {{ $cat->name }}
                    </label>
                @endforeach
            </div>
            @error('category_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="4"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">{{ old('description') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                Create Book
            </button>
            <a href="{{ route('books.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection