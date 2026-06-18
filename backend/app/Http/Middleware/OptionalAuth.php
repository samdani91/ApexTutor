<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptionalAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Attempt to authenticate via Sanctum if a token is present, but never block unauthenticated requests
        auth('sanctum')->check();
        return $next($request);
    }
}
