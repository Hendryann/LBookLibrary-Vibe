<?php

use App\Enums\Role;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows admin to delete a user', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);
    $member = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($admin)->delete(route('users.destroy', $member->id));

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseMissing('users', ['id' => $member->id]);
});

it('rejects non-admin attempting to delete a user', function () {
    $member = User::factory()->create(['role' => Role::MEMBER]);
    $other = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($member)->delete(route('users.destroy', $other->id));

    $response->assertStatus(403);
    $this->assertDatabaseHas('users', ['id' => $other->id]);
});

it('allows admin to view all users', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);
    User::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('users.index'));

    $response->assertOk();
});

it('rejects non-admin from viewing all users', function () {
    $member = User::factory()->create(['role' => Role::MEMBER]);

    $response = $this->actingAs($member)->get(route('users.index'));

    $response->assertStatus(403);
});