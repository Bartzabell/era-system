<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized action.');
        }

        // Split permissions by pipe (|) for OR logic
        $permissionArray = explode('|', $permissions);
        
        // Check if user has ANY of the permissions (OR logic)
        $hasPermission = false;
        foreach ($permissionArray as $permission) {
            if ($request->user()->hasPermission(trim($permission))) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
