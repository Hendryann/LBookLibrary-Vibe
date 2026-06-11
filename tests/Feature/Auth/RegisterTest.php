<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// ── Successful registration ───────────────────────────────────────────────

it('registers a new user with valid data and redirects to dashboard', function () {
    $response = $this->post(route('auth.register.submit'), [
        'name'                  => 'Jane Austen',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
});

it('creates a database record for the new user', function () {
    $this->post(route('auth.register.submit'), [
        'name'                  => 'Jane Austen',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('users', [
        'name'  => 'Jane Austen',
        'email' => 'jane@example.com',
    ]);
});

it('stores the password as a bcrypt hash, never plain text', function () {
    $this->post(route('auth.register.submit'), [
        'name'                  => 'Jane Austen',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'jane@example.com')->firstOrFail();

    expect($user->password)->not->toBe('password123');
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

it('assigns the MEMBER role to every newly registered user', function () {
    $this->post(route('auth.register.submit'), [
        'name'                  => 'Jane Austen',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'jane@example.com')->firstOrFail();

    expect($user->role)->toBe(Role::MEMBER);
});

it('authenticates the user immediately after registration', function () {
    $this->post(route('auth.register.submit'), [
        'name'                  => 'Jane Austen',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
});

// ── Validation failures ───────────────────────────────────────────────────

it('rejects registration when name is missing', function () {
    $response = $this->post(route('auth.register.submit'), [
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('name');
    $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
});

it('rejects registration when email is missing', function () {
    $response = $this->post(route('auth.register.submit'), [
        'name'                  => 'Jane Austen',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

it('rejects registration when email format is invalid', function () {
    $response = $this->post(route('auth.register.submit'), [
        'name'                  => 'Jane Austen',
        'email'                 => 'not-an-email',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

it('rejects registration when email is already taken', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    $response = $this->post(route('auth.register.submit'), [
        'name'                  => 'Jane Austen',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    expect(User::where('email', 'jane@example.com')->count())->toBe(1);
});

it('rejects registration when password is missing', function () {
    $response = $this->post(route('auth.register.submit'), [
        'name'  => 'Jane Austen',
        'email' => 'jane@example.com',
    ]);

    $response->assertSessionHasErrors('password');
});

it('rejects registration when password is shorter than 8 characters', function () {
    $response = $this->post(route('auth.register.submit'), [
        'name'                  => 'Jane Austen',
        'email'                 => 'jane@example.com',
        'password'              => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors('password');
});

it('rejects registration when password confirmation does not match', function () {
    $response = $this->post(route('auth.register.submit'), [
        'name'                  => 'Jane Austen',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'different456',
    ]);

    $response->assertSessionHasErrors('password');
});

// ── Guest-only gate ───────────────────────────────────────────────────────

it('redirects authenticated users away from the register page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
         ->get(route('auth.register'))
         ->assertRedirect();
});