<?php

namespace App\Repositories;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository
{

    public function getForUser(int $userId): Collection
    {
        return Transaction::with('copy.book')
            ->where('user_id', $userId)
            ->orderByDesc('borrow_date')
            ->get();
    }

    public function find(int $id): ?Transaction
    {
        return Transaction::find($id);
    }
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function findById(int $id): ?Transaction
    {
        return Transaction::with(['user', 'copy.book.author'])->find($id);
    }

    public function findActiveByCopyId(int $copyId): ?Transaction
    {
        return Transaction::query()
            ->where('copy_id', $copyId)
            ->where('status', TransactionStatus::ACTIVE)
            ->whereNull('return_date')
            ->first();
    }

    public function findActiveByUserAndBook(int $userId, int $bookId): ?Transaction
    {
        return Transaction::query()
            ->where('user_id', $userId)
            ->whereIn('status', [TransactionStatus::ACTIVE, TransactionStatus::OVERDUE])
            ->whereNull('return_date')
            ->whereHas('copy', fn ($q) => $q->where('book_id', $bookId))
            ->first();
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction->fresh(['user', 'copy.book.author']);
    }

    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['copy.book.author'])
            ->where('user_id', $userId)
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['user', 'copy.book.author'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Transactions that are logically overdue right now, regardless of stored status.
     */
    public function overdueCandidates()
    {
        return Transaction::query()
            ->whereNull('return_date')
            ->where('due_date', '<', Carbon::now())
            ->whereIn('status', [TransactionStatus::ACTIVE, TransactionStatus::OVERDUE])
            ->get();
    }

    public function paginateOverdue(int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['user', 'copy.book.author'])
            ->whereNull('return_date')
            ->where('due_date', '<', Carbon::now())
            ->where('status', TransactionStatus::OVERDUE)
            ->latest('due_date')
            ->paginate($perPage);
    }
}