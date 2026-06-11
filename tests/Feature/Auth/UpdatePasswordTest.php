<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// ── Successful password update ────────────────────────────────────────────

it('updates the password when the current password is correct', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password-1'),
    ]);

    $response = $this->actingAs($user)
                     ->put(route('auth.password.update'), [
                         'current_password'          => 'old-password-1',
                         'password'                  => 'new-password-1',
                         'password_confirmation'     => 'new-password-1',
                     ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

it('persists the new password as a bcrypt hash in the database', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password-1'),
    ]);

    $this->actingAs($user)
         ->put(route('auth.password.update'), [
             'current_password'      => 'old-password-1',
             'password'              => 'new-password-1',
             'password_confirmation' => 'new-password-1',
         ]);

    $user->refresh();

    expect(Hash::check('new-password-1', $user->password))->toBeTrue();
    expect(Hash::check('old-password-1', $user->password))->toBeFalse();
});

// ── Wrong current password ────────────────────────────────────────────────

it('rejects the update when current password is wrong', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    $response = $this->actingAs($user)
                     ->put(route('auth.password.update'), [
                         'current_password'      => 'wrong-password',
                         'password'              => 'new-password-1',
                         'password_confirmation' => 'new-password-1',
                     ]);

    $response->assertSessionHasErrors('current_password');
});

it('does not change the password when current password verification fails', function () {
    $originalHash = Hash::make('correct-password');

    $user = User::factory()->create(['password' => $originalHash]);

    $this->actingAs($user)
         ->put(route('auth.password.update'), [
             'current_password'      => 'wrong-password',
             'password'              => 'new-password-1',
             'password_confirmation' => 'new-password-1',
         ]);

    $user->refresh();

    expect(Hash::check('correct-password', $user->password))->toBeTrue();
});

// ── Validation failures ───────────────────────────────────────────────────

it('rejects update when current_password field is missing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
                     ->put(route('auth.password.update'), [
                         'password'              => 'new-password-1',
                         'password_confirmation' => 'new-password-1',
                     ]);

    $response->assertSessionHasErrors('current_password');
});

it('rejects update when new password is too short', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    $response = $this->actingAs($user)
                     ->put(route('auth.password.update'), [
                         'current_password'      => 'correct-password',
                         'password'              => 'short',
                         'password_confirmation' => 'short',
                     ]);

    $response->assertSessionHasErrors('password');
});

it('rejects update when new password confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    $response = $this->actingAs($user)
                     ->put(route('auth.password.update'), [
                         'current_password'      => 'correct-password',
                         'password'              => 'new-password-1',
                         'password_confirmation' => 'different-password',
                     ]);

    $response->assertSessionHasErrors('password');
});

it('rejects update when new password field is missing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
                     ->put(route('auth.password.update'), [
                         'current_password' => 'correct-password',
                     ]);

    $response->assertSessionHasErrors('password');
});

// ── Authentication guard ──────────────────────────────────────────────────

it('redirects unauthenticated users away from the password update route', function () {
    $response = $this->put(route('auth.password.update'), [
        'current_password'      => 'old-password-1',
        'password'              => 'new-password-1',
        'password_confirmation' => 'new-password-1',
    ]);

    $response->assertRedirect(route('login'));
});