{{--
    Reusable "Reserve" control for a book detail page.

    This is NOT wired into the existing Domain 2 book show view automatically
    (that file was not provided). To use it, include it from the book detail
    Blade view, passing the variables below:

        @include('reservations.partials.reserve-button', [
            'bookId' => $book->id,
            'hasAvailableCopy' => $book->copies()->where('status', \App\Enums\CopyStatus::AVAILABLE)->exists(),
            'activeReservation' => $book->reservations()
                ->where('user_id', auth()->id())
                ->where('status', \App\Enums\ReservationStatus::PENDING)
                ->first(),
        ])
--}}
<div id="reservation-widget-{{ $bookId }}" class="rounded-lg border border-gray-200 p-4">
    @auth
        @if ($hasAvailableCopy)
            <p class="text-sm text-gray-500">This book currently has available copies — no need to reserve.</p>
        @elseif ($activeReservation)
            <p class="text-sm text-gray-700">
                You are <span class="font-medium">#{{ $activeReservation->queue_position }}</span> in the reservation queue.
            </p>
            <form method="POST" action="{{ route('reservations.cancel', $activeReservation->id) }}" class="mt-2">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700"
                    onclick="return confirm('Cancel this reservation?')">
                    Cancel Reservation
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('reservations.store') }}">
                @csrf
                <input type="hidden" name="book_id" value="{{ $bookId }}">
                <button type="submit"
                    class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">
                    Reserve this Book
                </button>
            </form>
        @endif
    @else
        <p class="text-sm text-gray-500">
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-900">Log in</a> to reserve this book.
        </p>
    @endauth
</div>
