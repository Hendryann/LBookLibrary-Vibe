<?php

namespace App\Services;

use App\Enums\CopyStatus;
use App\Enums\TransactionStatus;
use App\Exceptions\BorrowingException;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\BookCopyRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public const BORROW_PERIOD_DAYS = 14;
    public const EXTENSION_DAYS = 7;
    public const FINE_PER_DAY = 1.5;

    public function __construct(
        protected TransactionRepository $transactions,
        protected BookCopyRepository $copies,
    ) {}

    public function borrow(User $user, int $copyId): Transaction
    {
        $copy = $this->copies->findById($copyId);

        if (! $copy) {
            throw BorrowingException::copyNotFound();
        }

        if ($copy->status !== CopyStatus::AVAILABLE) {
            throw BorrowingException::copyUnavailable();
        }

        if ($this->transactions->findActiveByCopyId($copy->id)) {
            throw BorrowingException::copyUnavailable();
        }

        if ($this->transactions->findActiveByUserAndBook($user->id, $copy->book_id)) {
            throw BorrowingException::duplicateActiveBorrow();
        }

        return DB::transaction(function () use ($user, $copy) {
            $transaction = $this->transactions->create([
                'user_id' => $user->id,
                'copy_id' => $copy->id,
                'borrow_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays(self::BORROW_PERIOD_DAYS),
                'return_date' => null,
                'fine_amount' => 0,
                'status' => TransactionStatus::ACTIVE,
            ]);

            $this->copies->updateStatus($copy, CopyStatus::BORROWED);

            return $transaction;
        });
    }

    public function returnBook(User $user, int $transactionId): Transaction
    {
        $transaction = $this->transactions->findById($transactionId);

        if (! $transaction) {
            throw BorrowingException::transactionNotFound();
        }

        $this->assertOwnerOrStaff($user, $transaction);

        if ($transaction->status === TransactionStatus::RETURNED || $transaction->return_date !== null) {
            throw BorrowingException::alreadyReturned();
        }

        $now = Carbon::now();
        $fine = $this->calculateFine($transaction, $now);

        return DB::transaction(function () use ($transaction, $now, $fine) {
            $updated = $this->transactions->update($transaction, [
                'return_date' => $now,
                'status' => TransactionStatus::RETURNED,
                'fine_amount' => $fine,
            ]);

            $copy = $this->copies->findById($transaction->copy_id);
            if ($copy) {
                $this->copies->updateStatus($copy, CopyStatus::AVAILABLE);
            }

            return $updated;
        });
    }

    public function extend(User $user, int $transactionId, int $extraDays = self::EXTENSION_DAYS): Transaction
    {
        $transaction = $this->transactions->findById($transactionId);

        if (! $transaction) {
            throw BorrowingException::transactionNotFound();
        }

        $this->assertOwnerOrStaff($user, $transaction);

        // Recompute current logical status before deciding eligibility.
        $this->syncSingleStatus($transaction);

        if ($transaction->status === TransactionStatus::RETURNED) {
            throw BorrowingException::extensionNotAllowed('the loan has already been returned.');
        }

        if ($transaction->status === TransactionStatus::OVERDUE) {
            throw BorrowingException::extensionNotAllowed('the loan is overdue and must be returned or settled first.');
        }

        $newDueDate = Carbon::parse($transaction->due_date)->addDays($extraDays);

        return $this->transactions->update($transaction, [
            'due_date' => $newDueDate,
        ]);
    }

    public function find(User $user, int $transactionId): Transaction
    {
        $transaction = $this->transactions->findById($transactionId);

        if (! $transaction) {
            throw BorrowingException::transactionNotFound();
        }

        $this->assertOwnerOrStaff($user, $transaction);

        return $transaction;
    }

    public function listForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->transactions->paginateForUser($user->id, $perPage);
    }

    public function listAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->transactions->paginateAll($perPage);
    }

    public function overdue(int $perPage = 15): LengthAwarePaginator
    {
        $this->syncOverdueStatuses();

        return $this->transactions->paginateOverdue($perPage);
    }

    /**
     * Synchronize stored status with the logical overdue condition for all
     * still-open transactions, and refresh the running fine estimate.
     */
    public function syncOverdueStatuses(): void
    {
        $candidates = $this->transactions->overdueCandidates();

        foreach ($candidates as $transaction) {
            $this->syncSingleStatus($transaction);
        }
    }

    protected function syncSingleStatus(Transaction $transaction): void
    {
        if ($transaction->return_date !== null) {
            return;
        }

        $isOverdue = Carbon::now()->greaterThan(Carbon::parse($transaction->due_date));

        if ($isOverdue && $transaction->status !== TransactionStatus::OVERDUE) {
            $this->transactions->update($transaction, [
                'status' => TransactionStatus::OVERDUE,
                'fine_amount' => $this->calculateFine($transaction, Carbon::now()),
            ]);
        } elseif ($isOverdue) {
            // Already OVERDUE: keep the running fine estimate current.
            $this->transactions->update($transaction, [
                'fine_amount' => $this->calculateFine($transaction, Carbon::now()),
            ]);
        }
    }

    /**
     * Fine is never negative and only accrues once the due date has passed.
     */
    public function calculateFine(Transaction $transaction, ?Carbon $asOf = null): float
    {
        $asOf ??= Carbon::now();
        $dueDate = Carbon::parse($transaction->due_date);

        if ($asOf->lessThanOrEqualTo($dueDate)) {
            return 0.0;
        }

        $overdueDays = $dueDate->diffInDays($asOf);

        return round(max(0, $overdueDays) * self::FINE_PER_DAY, 2);
    }

    protected function assertOwnerOrStaff(User $user, Transaction $transaction): void
    {
        $isOwner = $transaction->user_id === $user->id;
        $isStaff = in_array($user->role->value ?? $user->role, ['ADMIN', 'LIBRARIAN'], true);

        if (! $isOwner && ! $isStaff) {
            throw BorrowingException::unauthorizedAction();
        }
    }
}