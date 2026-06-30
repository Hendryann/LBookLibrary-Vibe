<?php

namespace App\Http\Controllers;

use App\Exceptions\ReservationException;
use App\Http\Requests\StoreReservationRequest;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservationService)
    {
    }

    /**
     * GET /reservations
     * Members see their own reservations; staff (ADMIN/LIBRARIAN) see all.
     */
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        $reservations = $this->reservationService->listForViewer($user);

        if ($request->wantsJson()) {
            return response()->json(['data' => $reservations]);
        }

        return view('reservations.index', ['reservations' => $reservations]);
    }

    /**
     * POST /reservations
     */
    public function store(StoreReservationRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        try {
            $reservation = $this->reservationService->create($user, (int) $request->validated('book_id'));
        } catch (ReservationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->status());
            }

            return back()->withErrors(['reservation' => $e->getMessage()])->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json(['data' => $reservation], 201);
        }

        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Reservation created successfully.');
    }

    /**
     * GET /reservations/{id}
     */
    public function show(Request $request, int $id): View|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        try {
            $reservation = $this->reservationService->findForViewer($id, $user);
        } catch (ReservationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->status());
            }

            abort($e->status(), $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['data' => $reservation]);
        }

        return view('reservations.show', ['reservation' => $reservation]);
    }

    /**
     * PATCH /reservations/{id}/cancel
     */
    public function cancel(Request $request, int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        try {
            $reservation = $this->reservationService->cancel($id, $user);
        } catch (ReservationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->status());
            }

            return back()->withErrors(['reservation' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json(['data' => $reservation]);
        }

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation cancelled successfully.');
    }

    /**
     * GET /books/{id}/reservations
     */
    public function bookReservations(Request $request, int $bookId): View|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        try {
            $reservations = $this->reservationService->listForBook($bookId, $user);
        } catch (ReservationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->status());
            }

            abort($e->status(), $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['data' => $reservations]);
        }

        return view('reservations.book', ['reservations' => $reservations, 'bookId' => $bookId]);
    }
}
