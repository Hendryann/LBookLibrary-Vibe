@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-red-700 mb-6">Overdue Transactions</h1>

    @if ($transactions->isEmpty())
        <p class="text-gray-500">No overdue transactions.</p>
    @else
        <div class="overflow-x-auto rounded-lg border border-red-200">
            <table class="min-w-full divide-y divide-red-100 text-sm">
                <thead class="bg-red-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-red-700">User</th>
                        <th class="px-4 py-3 text-left font-medium text-red-700">Book</th>
                        <th class="px-4 py-3 text-left font-medium text-red-700">Due</th>
                        <th class="px-4 py-3 text-left font-medium text-red-700">Fine</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-red-50">
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td class="px-4 py-3">{{ $transaction->user->name }}</td>
                            <td class="px-4 py-3">{{ $transaction->copy->book->title ?? '—' }}</td>
                            <td class="px-4 py-3">{{ optional($transaction->due_date)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-medium text-red-700">${{ number_format($transaction->fine_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection