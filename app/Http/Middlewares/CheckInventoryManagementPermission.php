<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInventoryManagementPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, [Role::ADMIN, Role::LIBRARIAN], true)) {
            abort(403, 'Unauthorized to manage inventory.');
        }

        return $next($request);
    }
}