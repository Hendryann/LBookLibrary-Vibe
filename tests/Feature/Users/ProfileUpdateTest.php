<?php

use App\Enums\Role;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows a user to update their own profile', function () {
    $user = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($user)
        ->put(route('users.update', $user->id), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

    $response->assertRedirect(route('users.show', $user->id));
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('rejects duplicate email on profile update', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($user)
        ->put(route('users.update', $user->id), [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ]);

    $response->assertSessionHasErrors('email');
    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
        'email' => 'taken@example.com',
    ]);
});

it('rejects unauthorized profile modification', function () {
    $owner = User::factory()->create(['role' => Role::MEMBER]);
    $other = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($other)
        ->put(route('users.update', $owner->id), [
            'name' => 'Hacked Name',
            'email' => 'hacked@example.com',
        ]);

    $response->assertForbidden();
    $this->assertDatabaseMissing('users', [
        'id' => $owner->id,
        'name' => 'Hacked Name',
    ]);
});

it('allows admin to update another users profile', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);
    $member = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($admin)
        ->put(route('users.update', $member->id), [
            'name' => 'Admin Updated',
            'email' => $member->email,
        ]);

    $response->assertRedirect(route('users.show', $member->id));
    $this->assertDatabaseHas('users', [
        'id' => $member->id,
        'name' => 'Admin Updated',
    ]);
});

it('returns 404 when updating a non-existent user', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);

    $response = $this->actingAs($admin)
        ->put(route('users.update', 999999), [
            'name' => 'Ghost',
            'email' => 'ghost@example.com',
        ]);

    $response->assertStatus(404);
});