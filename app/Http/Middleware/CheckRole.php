<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized');
        }

        $userRole = $request->user()->role;

        // Check if user's role is in the allowed roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
