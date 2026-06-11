<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// ── Successful login ──────────────────────────────────────────────────────

it('logs in a user with valid credentials and redirects to dashboard', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    $response = $this->post(route('auth.login.submit'), [
        'email'    => $user->email,
        'password' => 'correct-password',
    ]);

    $response->assertRedirect(route('dashboard'));
});

it('authenticates the user session after a successful login', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    $this->post(route('auth.login.submit'), [
        'email'    => $user->email,
        'password' => 'correct-password',
    ]);

    $this->assertAuthenticatedAs($user);
});

// ── Invalid credentials ───────────────────────────────────────────────────

it('rejects login when the password is incorrect', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    $response = $this->post(route('auth.login.submit'), [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('rejects login when the email does not exist', function () {
    $response = $this->post(route('auth.login.submit'), [
        'email'    => 'nobody@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

// ── Validation failures ───────────────────────────────────────────────────

it('rejects login when email field is empty', function () {
    $response = $this->post(route('auth.login.submit'), [
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

it('rejects login when email format is invalid', function () {
    $response = $this->post(route('auth.login.submit'), [
        'email'    => 'not-an-email',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

it('rejects login when password field is empty', function () {
    $response = $this->post(route('auth.login.submit'), [
        'email' => 'user@example.com',
    ]);

    $response->assertSessionHasErrors('password');
});

// ── Guest-only gate ───────────────────────────────────────────────────────

it('redirects authenticated users away from the login page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
         ->get(route('login'))
         ->assertRedirect();
});

// ── Login page renders ────────────────────────────────────────────────────

it('renders the login page for guests', function () {
    $this->get(route('login'))
         ->assertOk()
         ->assertViewIs('auth.login');
});