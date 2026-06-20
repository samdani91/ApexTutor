<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptionalAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Attempt to authenticate via Sanctum; if successful, bind the user onto the
        // default guard so $request->user() works in controllers without blocking guests.
        if ($user = auth('sanctum')->user()) {
            auth()->setUser($user);
        }
        return $next($request);
    }
}
