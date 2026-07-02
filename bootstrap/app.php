<?php

use App\Exceptions\BookNotFoundException;
use App\Exceptions\DuplicateReviewException;
use App\Exceptions\ReviewNotFoundException;
use App\Exceptions\UnauthorizedActionException;
use App\Exceptions\UserNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/auth/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UserNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
            abort(404, $e->getMessage());
        });

        $exceptions->render(function (BookNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
            abort(404, $e->getMessage());
        });

        $exceptions->render(function (ReviewNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 404);
            }
            abort(404, $e->getMessage());
        });

        $exceptions->render(function (DuplicateReviewException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 409);
            }
            return back()->withErrors(['review' => $e->getMessage()])->setStatusCode(409);
        });

        $exceptions->render(function (UnauthorizedActionException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 403);
            }
            abort(403, $e->getMessage());
        });
    })->create();
