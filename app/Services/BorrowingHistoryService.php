<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use Illuminate\Database\Eloquent\Collection;

class BorrowingHistoryService
{
    public function __construct(
        protected TransactionRepository $transactionRepository
    ) {}

    public function getHistoryForUser(int $userId): array
    {
        $transactions = $this->transactionRepository->getForUser($userId);

        return [
            'active' => $transactions->where('status', \App\Enums\TransactionStatus::ACTIVE)->values(),
            'returned' => $transactions->where('status', \App\Enums\TransactionStatus::RETURNED)->values(),
            'overdue' => $transactions->where('status', \App\Enums\TransactionStatus::OVERDUE)->values(),
            'total_fines' => $transactions->sum('fine_amount'),
        ];
    }
}