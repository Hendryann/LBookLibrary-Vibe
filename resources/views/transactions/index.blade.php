@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $isStaff ? 'All Transactions' : 'My Loans' }}</h1>
        @if ($isStaff)
            <a href="{{ route('transactions.overdue') }}" class="text-sm text-red-600 hover:underline">View overdue</a>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if ($transactions->isEmpty())
        <div class="text-center py-16 text-gray-500">
            No transactions found.
        </div>
    @else
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        @if ($isStaff)<th class="px-4 py-3 text-left font-medium text-gray-600">User</th>@endif
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Book</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Borrowed</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Due</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Returned</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Fine</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($transactions as $transaction)
                        <tr>
                            @if ($isStaff)
                                <td class="px-4 py-3">{{ $transaction->user->name }}</td>
                            @endif
                            <td class="px-4 py-3">
                                <a href="{{ route('transactions.show', $transaction->id) }}" class="text-indigo-600 hover:underline">
                                    {{ $transaction->copy->book->title ?? 'Unknown title' }}
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ optional($transaction->borrow_date)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ optional($transaction->due_date)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ $transaction->return_date ? $transaction->return_date->format('Y-m-d') : '—' }}</td>
                            <td class="px-4 py-3">${{ number_format($transaction->fine_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex px-2 py-1 rounded-full text-xs font-medium',
                                    'bg-blue-100 text-blue-700' => $transaction->status->value === 'ACTIVE',
                                    'bg-green-100 text-green-700' => $transaction->status->value === 'RETURNED',
                                    'bg-red-100 text-red-700' => $transaction->status->value === 'OVERDUE',
                                ])>{{ $transaction->status->value }}</span>
                            </td>
                            <td class="px-4 py-3 space-x-2 whitespace-nowrap">
                                @if (is_null($transaction->return_date))
                                    <form action="{{ route('transactions.return', $transaction->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700">Return</button>
                                    </form>
                                    <form action="{{ route('transactions.extend', $transaction->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            @disabled($transaction->status->value === 'OVERDUE')
                                            class="text-xs px-2 py-1 rounded bg-amber-500 text-white hover:bg-amber-600 disabled:opacity-40 disabled:cursor-not-allowed">
                                            Extend
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Completed</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection