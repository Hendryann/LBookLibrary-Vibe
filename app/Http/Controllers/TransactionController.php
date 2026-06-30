<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Exceptions\BorrowingException;
use App\Http\Requests\BorrowBookRequest;
use App\Http\Requests\ExtendTransactionRequest;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactions) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $isStaff = in_array($user->role, [Role::ADMIN, Role::LIBRARIAN], true);

        $this->transactions->syncOverdueStatuses();

        $list = $isStaff
            ? $this->transactions->listAll()
            : $this->transactions->listForUser($user);

        return view('transactions.index', [
            'transactions' => $list,
            'isStaff' => $isStaff,
        ]);
    }

    public function show(Request $request, int $id): View|RedirectResponse
    {
        try {
            $transaction = $this->transactions->find($request->user(), $id);
        } catch (BorrowingException $e) {
            return redirect()
                ->route('transactions.index')
                ->with('error', $e->getMessage());
        }

        return view('transactions.show', [
            'transaction' => $transaction,
        ]);
    }

    public function borrow(BorrowBookRequest $request): RedirectResponse
    {
        try {
            $this->transactions->borrow($request->user(), (int) $request->validated('copy_id'));
        } catch (BorrowingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Book borrowed successfully.');
    }

    public function returnBook(Request $request, int $id): RedirectResponse
    {
        try {
            $this->transactions->returnBook($request->user(), $id);
        } catch (BorrowingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Book returned successfully.');
    }

    public function extend(ExtendTransactionRequest $request, int $id): RedirectResponse
    {
        try {
            $this->transactions->extend($request->user(), $id, $request->extraDays());
        } catch (BorrowingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Loan extended successfully.');
    }

    public function overdue(Request $request): View
    {
        $user = $request->user();
        $isStaff = in_array($user->role, [Role::ADMIN, Role::LIBRARIAN], true);

        abort_unless($isStaff, 403, 'Only administrators or librarians may view overdue transactions.');

        $list = $this->transactions->overdue();

        return view('transactions.overdue', [
            'transactions' => $list,
        ]);
    }
}