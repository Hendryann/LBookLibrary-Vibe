<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Factory for the User model.
 *
 * Deliberately omits email_verified_at and remember_token — those columns
 * do not exist in this project's schema. Only fields present in the users
 * table may appear here: id, name, email, password, role, created_at, updated_at.
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Cached hash so repeated factory calls within one test run do not
     * re-bcrypt the same plain-text string thousands of times.
     */
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name'     => fake()->name(),
            'email'    => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role'     => Role::MEMBER,
        ];
    }
}