<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->bearerToken() && $request->hasCookie('auth_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('auth_token'));
        }
        return $next($request);
    }
}
