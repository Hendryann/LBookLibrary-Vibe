<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateProfileRequest;
use App\Services\BorrowingHistoryService;
use App\Services\RecommendationService;
use App\Services\UserProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        protected UserProfileService $userProfileService,
        protected BorrowingHistoryService $borrowingHistoryService,
        protected RecommendationService $recommendationService
    ) {}

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $users = $this->userProfileService->listAll();

        return view('users.index', compact('users'));
    }

    public function show(Request $request, int $id)
    {
        $user = $this->userProfileService->findOrFail($id);

        return view('users.show', compact('user'));
    }

    public function update(UpdateProfileRequest $request, int $id)
    {
        $user = $this->userProfileService->updateProfile($id, $request->validated());

        return redirect()
            ->route('users.show', $user->id)
            ->with('success', 'Profile updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->userProfileService->deleteUser($id, Auth::user());

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function history(Request $request)
    {
        $history = $this->borrowingHistoryService->getHistoryForUser(Auth::id());

        return view('users.history', compact('history'));
    }

    public function recommendations(Request $request)
    {
        $recommendations = $this->recommendationService->recommendForUser($request->user()->id);

        return view('users.recommendations', compact('recommendations'));
    }

    protected function authorizeAdmin(Request $request): void
    {
        if ($request->user()->role !== \App\Enums\Role::ADMIN) {
            throw new \App\Exceptions\UnauthorizedActionException('Only administrators may view all users.');
        }
    }
}