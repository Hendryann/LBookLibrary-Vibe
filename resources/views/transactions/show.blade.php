@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('transactions.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Back</a>

    <h1 class="text-2xl font-bold text-gray-900 mt-4 mb-6">Transaction #{{ $transaction->id }}</h1>

    @if (session('error'))
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <dl class="bg-white border border-gray-200 rounded-lg divide-y divide-gray-100">
        <div class="flex justify-between px-4 py-3"><dt class="text-gray-500">Book</dt><dd class="font-medium">{{ $transaction->copy->book->title ?? '—' }}</dd></div>
        <div class="flex justify-between px-4 py-3"><dt class="text-gray-500">Copy</dt><dd>{{ $transaction->copy->barcode }}</dd></div>
        <div class="flex justify-between px-4 py-3"><dt class="text-gray-500">Borrow date</dt><dd>{{ optional($transaction->borrow_date)->format('Y-m-d') }}</dd></div>
        <div class="flex justify-between px-4 py-3"><dt class="text-gray-500">Due date</dt><dd>{{ optional($transaction->due_date)->format('Y-m-d') }}</dd></div>
        <div class="flex justify-between px-4 py-3"><dt class="text-gray-500">Return date</dt><dd>{{ $transaction->return_date ? $transaction->return_date->format('Y-m-d') : '—' }}</dd></div>
        <div class="flex justify-between px-4 py-3"><dt class="text-gray-500">Fine</dt><dd>${{ number_format($transaction->fine_amount, 2) }}</dd></div>
        <div class="flex justify-between px-4 py-3"><dt class="text-gray-500">Status</dt><dd>{{ $transaction->status->value }}</dd></div>
    </dl>

    @if (is_null($transaction->return_date))
        <div class="mt-6 flex gap-3">
            <form action="{{ route('transactions.return', $transaction->id) }}" method="POST">
                @csrf @method('PATCH')
                <button class="px-4 py-2 rounded bg-emerald-600 text-white text-sm hover:bg-emerald-700">Return book</button>
            </form>
            <form action="{{ route('transactions.extend', $transaction->id) }}" method="POST">
                @csrf @method('PATCH')
                <button @disabled($transaction->status->value === 'OVERDUE')
                    class="px-4 py-2 rounded bg-amber-500 text-white text-sm hover:bg-amber-600 disabled:opacity-40">
                    Extend loan
                </button>
            </form>
        </div>
    @endif
</div>
@endsection