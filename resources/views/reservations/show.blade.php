@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('reservations.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to my reservations</a>

    <div class="mt-4 rounded-lg border border-gray-200 bg-white p-6">
        <h1 class="text-xl font-semibold text-gray-900">{{ $reservation->book?->title }}</h1>
        <p class="mt-1 text-sm text-gray-500">Reserved on {{ $reservation->reserved_at?->format('Y-m-d H:i') }}</p>

        <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd class="font-medium text-gray-900">{{ $reservation->status->value }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Queue Position</dt>
                <dd class="font-medium text-gray-900">
                    {{ $reservation->status === \App\Enums\ReservationStatus::PENDING ? $reservation->queue_position : '—' }}
                </dd>
            </div>
        </dl>

        @if ($reservation->status === \App\Enums\ReservationStatus::PENDING)
            <form method="POST" action="{{ route('reservations.cancel', $reservation->id) }}" class="mt-6">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    onclick="return confirm('Cancel this reservation?')">
                    Cancel Reservation
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
