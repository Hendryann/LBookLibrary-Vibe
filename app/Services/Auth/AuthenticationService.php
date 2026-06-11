<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\Role;
use App\Models\User;
use App\Repositories\Auth\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Register a new user with the MEMBER role.
     * Password is hashed before persistence.
     */
    public function register(array $data): User
    {
        return $this->userRepository->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => Role::MEMBER,
        ]);
    }

    /**
     * Validate credentials and return the authenticated user.
     * Throws a ValidationException on invalid credentials.
     */
    public function login(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        return $user;
    }

    /**
     * Update the user's password after verifying the current one.
     * Throws a ValidationException when current password does not match.
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('auth.password')],
            ]);
        }

        $this->userRepository->updatePassword($user, Hash::make($newPassword));
    }
}