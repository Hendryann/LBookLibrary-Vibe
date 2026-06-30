@extends('layouts.app')

@section('title', 'Add Category — Bibliotheca')

@section('content')
<div class="mb-4">
    <a href="{{ route('categories.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to Categories</a>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Category</h1>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('categories.store') }}" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('name') border-red-400 @enderror">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                Create Category
            </button>
            <a href="{{ route('categories.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection