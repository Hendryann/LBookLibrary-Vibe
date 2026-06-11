<?php

declare(strict_types=1);

use App\Models\User;

// ── Successful logout ─────────────────────────────────────────────────────

it('logs out an authenticated user and redirects to login', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
                     ->post(route('auth.logout'));

    $response->assertRedirect(route('login'));
});

it('de-authenticates the user after logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
         ->post(route('auth.logout'));

    $this->assertGuest();
});

it('regenerates the CSRF token after logout', function () {
    $user = User::factory()->create();

    $before = $this->actingAs($user)->get(route('dashboard'));
    $tokenBefore = session()->token();

    $this->post(route('auth.logout'));

    // Session was invalidated — a new token is generated
    $this->assertGuest();
    $this->assertFalse(session()->has('_token') && session()->token() === $tokenBefore);
});

// ── Guest protection ──────────────────────────────────────────────────────

it('redirects unauthenticated requests to the logout route back to login', function () {
    $response = $this->post(route('auth.logout'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});