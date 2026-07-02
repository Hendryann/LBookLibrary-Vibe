@extends('layouts.app')

@section('title', 'Borrowing History')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4">
    <h1 class="text-2xl font-semibold text-zinc-100 mb-6">Borrowing History</h1>

    <div class="mb-6 bg-zinc-900 border border-zinc-800 rounded-lg p-4">
        <p class="text-zinc-400 text-sm">Total Fines</p>
        <p class="text-xl text-amber-400 font-semibold">Rp {{ number_format($history['total_fines'], 0, ',', '.') }}</p>
    </div>

    @foreach (['active' => 'Active', 'overdue' => 'Overdue', 'returned' => 'Returned'] as $key => $label)
        <h2 class="text-lg font-medium text-zinc-200 mt-8 mb-3">{{ $label }} Transactions</h2>

        @if ($history[$key]->isEmpty())
            <p class="text-zinc-500 text-sm mb-4">No {{ strtolower($label) }} transactions.</p>
        @else
            <div class="overflow-x-auto rounded-lg border border-zinc-800 mb-4">
                <table class="min-w-full divide-y divide-zinc-800">
                    <thead class="bg-zinc-900">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Book</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Borrow Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Due Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Return Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Fine</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @foreach ($history[$key] as $transaction)
                            <tr>
                                <td class="px-4 py-2 text-zinc-200">{{ $transaction->copy->book->title ?? '—' }}</td>
                                <td class="px-4 py-2 text-zinc-400">{{ $transaction->borrow_date }}</td>
                                <td class="px-4 py-2 text-zinc-400">{{ $transaction->due_date }}</td>
                                <td class="px-4 py-2 text-zinc-400">{{ $transaction->return_date ?? '—' }}</td>
                                <td class="px-4 py-2 text-zinc-400">Rp {{ number_format($transaction->fine_amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach
</div>
@endsection