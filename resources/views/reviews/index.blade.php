@extends('layouts.app')

@section('title', 'Book Reviews')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-semibold text-zinc-100 mb-6">Reviews</h1>

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

    <form method="POST" action="{{ route('books.reviews.store', $bookId) }}" class="bg-zinc-900 border border-zinc-800 rounded-lg p-4 mb-6 space-y-3">
        @csrf
        <div>
            <label class="block text-sm text-zinc-400 mb-1">Rating (1-5)</label>
            <select name="rating" class="w-full rounded-md bg-zinc-800 border border-zinc-700 text-zinc-100 px-3 py-2">
                @for ($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-sm text-zinc-400 mb-1">Comment (optional)</label>
            <textarea name="comment" rows="3" class="w-full rounded-md bg-zinc-800 border border-zinc-700 text-zinc-100 px-3 py-2"></textarea>
        </div>
        <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-zinc-950 font-medium px-4 py-2 rounded-md transition">
            Submit Review
        </button>
    </form>

    @if ($reviews->isEmpty())
        <p class="text-zinc-500">No reviews yet. Be the first to review this book.</p>
    @else
        <div class="space-y-4">
            @foreach ($reviews as $review)
                <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-zinc-200 font-medium">{{ $review->user->name }}</p>
                            <p class="text-amber-400 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
                        </div>
                        @auth
                            @if (auth()->id() === $review->user_id || auth()->user()->role === \App\Enums\Role::ADMIN)
                                <form method="POST" action="{{ route('books.reviews.destroy', [$bookId, $review->id]) }}" onsubmit="return confirm('Delete this review?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 text-sm hover:underline">Delete</button>
                                </form>
                            @endif
                        @endauth
                    </div>
                    @if ($review->comment)
                        <p class="text-zinc-400 text-sm mt-2">{{ $review->comment }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection