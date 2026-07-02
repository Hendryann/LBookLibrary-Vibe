<?php

use App\Enums\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserProfileService;
use Illuminate\Validation\ValidationException;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new UserRepository();
    $this->service = new UserProfileService($this->repository);
});

it('updates the user profile with valid data', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $updated = $this->service->updateProfile($user->id, ['name' => 'New Name']);

    expect($updated->name)->toBe('New Name');
});

it('throws a validation exception when user does not exist', function () {
    expect(fn () => $this->service->findOrFail(999999))
        ->toThrow(ValidationException::class);
});

it('prevents non-admin from deleting another user', function () {
    $actor = User::factory()->create(['role' => Role::MEMBER]);
    $target = User::factory()->create(['role' => Role::MEMBER]);

    expect(fn () => $this->service->deleteUser($target->id, $actor))
        ->toThrow(ValidationException::class);
});

it('allows admin to delete a user', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN]);
    $target = User::factory()->create(['role' => Role::MEMBER]);

    $result = $this->service->deleteUser($target->id, $admin);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});