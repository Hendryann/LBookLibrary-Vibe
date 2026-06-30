@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-xl font-semibold text-gray-900 mb-4">Reservation Queue</h1>

    @if ($reservations->isEmpty())
        <div class="rounded-md border border-dashed border-gray-300 p-10 text-center text-gray-500">
            No active reservations for this book.
        </div>
    @else
        <ol class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
            @foreach ($reservations as $reservation)
                <li class="flex items-center justify-between px-4 py-3 text-sm">
                    <span class="font-medium text-gray-900">#{{ $reservation->queue_position }}</span>
                    <span class="text-gray-500">{{ $reservation->user?->name ?? 'Hidden' }}</span>
                    <span class="text-gray-500">{{ $reservation->status->value }}</span>
                </li>
            @endforeach
        </ol>
    @endif
</div>
@endsection
