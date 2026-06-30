<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Auth\UserRepository;
use App\Repositories\Auth\UserRepositoryInterface;
use App\Repositories\BookCopyRepository;
use App\Repositories\Contracts\BookCopyRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(
            BookCopyRepositoryInterface::class,
            BookCopyRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
