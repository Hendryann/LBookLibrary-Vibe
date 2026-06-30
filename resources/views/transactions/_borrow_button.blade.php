{{-- Include on a book/copy listing page: @include('transactions._borrow_button', ['copy' => $copy]) --}}
<form action="{{ route('transactions.borrow') }}" method="POST" class="inline">
    @csrf
    <input type="hidden" name="copy_id" value="{{ $copy->id }}">
    <button type="submit"
        @disabled($copy->status->value !== 'AVAILABLE')
        class="text-xs px-3 py-1.5 rounded bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed">
        {{ $copy->status->value === 'AVAILABLE' ? 'Borrow' : ucfirst(strtolower($copy->status->value)) }}
    </button>
</form>