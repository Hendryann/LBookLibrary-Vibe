<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;
use App\Repositories\Auth\UserRepositoryInterface;
use App\Services\Auth\AuthenticationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;

// ── Helpers ───────────────────────────────────────────────────────────────

function makeService(MockInterface $repository): AuthenticationService
{
    return new AuthenticationService($repository);
}

// ── register() ────────────────────────────────────────────────────────────

it('register passes name and email through to the repository unchanged', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $expected = new User(['name' => 'Jane Austen', 'email' => 'jane@example.com']);

    $repository->shouldReceive('create')
        ->once()
        ->withArgs(function (array $data) {
            return $data['name'] === 'Jane Austen'
                && $data['email'] === 'jane@example.com';
        })
        ->andReturn($expected);

    $result = $service->register([
        'name'     => 'Jane Austen',
        'email'    => 'jane@example.com',
        'password' => 'password123',
    ]);

    expect($result)->toBe($expected);
});

it('register hashes the password before passing it to the repository', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $repository->shouldReceive('create')
        ->once()
        ->withArgs(function (array $data) {
            // Must be a hash, never the raw plain-text value
            return $data['password'] !== 'password123'
                && Hash::check('password123', $data['password']);
        })
        ->andReturn(new User());

    $service->register([
        'name'     => 'Jane Austen',
        'email'    => 'jane@example.com',
        'password' => 'password123',
    ]);
});

it('register always assigns the MEMBER role', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $repository->shouldReceive('create')
        ->once()
        ->withArgs(function (array $data) {
            return $data['role'] === Role::MEMBER;
        })
        ->andReturn(new User());

    $service->register([
        'name'     => 'Jane Austen',
        'email'    => 'jane@example.com',
        'password' => 'password123',
    ]);
});

it('register returns the User instance produced by the repository', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $user = new User(['name' => 'Jane Austen']);

    $repository->shouldReceive('create')->once()->andReturn($user);

    $result = $service->register([
        'name'     => 'Jane Austen',
        'email'    => 'jane@example.com',
        'password' => 'password123',
    ]);

    expect($result)->toBe($user);
});

// ── login() ───────────────────────────────────────────────────────────────

it('login returns the user when credentials are valid', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $user = new User(['email' => 'jane@example.com']);
    $user->password = Hash::make('correct-password');

    $repository->shouldReceive('findByEmail')
        ->with('jane@example.com')
        ->once()
        ->andReturn($user);

    $result = $service->login('jane@example.com', 'correct-password');

    expect($result)->toBe($user);
});

it('login throws ValidationException when the email is not found', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $repository->shouldReceive('findByEmail')
        ->with('nobody@example.com')
        ->once()
        ->andReturn(null);

    expect(fn () => $service->login('nobody@example.com', 'password123'))
        ->toThrow(ValidationException::class);
});

it('login throws ValidationException when the password is wrong', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $user = new User(['email' => 'jane@example.com']);
    $user->password = Hash::make('correct-password');

    $repository->shouldReceive('findByEmail')
        ->with('jane@example.com')
        ->once()
        ->andReturn($user);

    expect(fn () => $service->login('jane@example.com', 'wrong-password'))
        ->toThrow(ValidationException::class);
});

it('login attaches the error to the email field in the ValidationException', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $repository->shouldReceive('findByEmail')->andReturn(null);

    try {
        $service->login('nobody@example.com', 'password123');
        $this->fail('Expected ValidationException was not thrown.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('email');
    }
});

// ── updatePassword() ──────────────────────────────────────────────────────

it('updatePassword calls the repository with a hashed new password', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $user           = new User();
    $user->password = Hash::make('old-password');

    $repository->shouldReceive('updatePassword')
        ->once()
        ->withArgs(function (User $u, string $hash) {
            return Hash::check('new-password', $hash);
        });

    $service->updatePassword($user, 'old-password', 'new-password');
});

it('updatePassword throws ValidationException when current password is wrong', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $user           = new User();
    $user->password = Hash::make('correct-password');

    $repository->shouldReceive('updatePassword')->never();

    expect(fn () => $service->updatePassword($user, 'wrong-password', 'new-password'))
        ->toThrow(ValidationException::class);
});

it('updatePassword attaches the error to current_password field in the exception', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $user           = new User();
    $user->password = Hash::make('correct-password');

    $repository->shouldReceive('updatePassword')->never();

    try {
        $service->updatePassword($user, 'wrong-password', 'new-password');
        $this->fail('Expected ValidationException was not thrown.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('current_password');
    }
});

it('updatePassword does not call the repository when current password fails', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $user           = new User();
    $user->password = Hash::make('correct-password');

    $repository->shouldReceive('updatePassword')->never();

    try {
        $service->updatePassword($user, 'wrong-password', 'new-password');
    } catch (ValidationException) {
        // Expected — the assertion is that updatePassword was never called
    }
});

it('updatePassword never stores the new password as plain text', function () {
    $repository = Mockery::mock(UserRepositoryInterface::class);
    $service    = makeService($repository);

    $user           = new User();
    $user->password = Hash::make('old-password');

    $repository->shouldReceive('updatePassword')
        ->once()
        ->withArgs(function (User $u, string $hash) {
            return $hash !== 'new-password';
        });

    $service->updatePassword($user, 'old-password', 'new-password');
});