<?php

namespace App\Services;

use App\Enums\Role;
use App\Exceptions\UnauthorizedActionException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserProfileService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function listAll(): Collection
    {
        return $this->userRepository->all();
    }

    public function findOrFail(int $id): User
    {
        $user = $this->userRepository->find($id);

        if (! $user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function updateProfile(int $id, array $data): User
    {
        $user = $this->findOrFail($id);

        return DB::transaction(function () use ($user, $data) {
            return $this->userRepository->update($user, $data);
        });
    }

    public function deleteUser(int $id, User $actingUser): bool
    {
        $user = $this->findOrFail($id);

        if ($actingUser->role !== Role::ADMIN) {
            throw new UnauthorizedActionException('Only administrators may delete users.');
        }

        return DB::transaction(function () use ($user) {
            return $this->userRepository->delete($user);
        });
    }
}