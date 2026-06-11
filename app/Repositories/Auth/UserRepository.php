<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function updatePassword(User $user, string $hashedPassword): void
    {
        $user->update(['password' => $hashedPassword]);
    }
}