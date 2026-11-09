<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role?->role_name, $roles, true)) {
            abort(403, 'Akses ini hanya untuk role yang berwenang.');
        }

        return $next($request);
    }
}
