<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateMobileToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Mobile-Token');
        
        if ($token !== env('MOBILE_APP_TOKEN')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid token.',
            ], 401);
        }

        return $next($request);
    }
}