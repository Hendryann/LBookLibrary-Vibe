@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">My Reservations</h1>

    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            {{ $errors->first('reservation') }}
        </div>
    @endif

    @if ($reservations->isEmpty())
        <div class="rounded-md border border-dashed border-gray-300 p-10 text-center text-gray-500">
            You have no reservations yet.
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reserved At</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Queue Position</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($reservations as $reservation)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $reservation->book?->title }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $reservation->reserved_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $reservation->status === \App\Enums\ReservationStatus::PENDING ? $reservation->queue_position : '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $statusClasses = match ($reservation->status) {
                                        \App\Enums\ReservationStatus::PENDING => 'bg-yellow-100 text-yellow-800',
                                        \App\Enums\ReservationStatus::FULFILLED => 'bg-green-100 text-green-800',
                                        \App\Enums\ReservationStatus::CANCELLED => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClasses }}">
                                    {{ $reservation->status->value }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('reservations.show', $reservation->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                @if ($reservation->status === \App\Enums\ReservationStatus::PENDING)
                                    <form method="POST" action="{{ route('reservations.cancel', $reservation->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Cancel this reservation?')">
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $reservations->links() }}
        </div>
    @endif
</div>
@endsection
