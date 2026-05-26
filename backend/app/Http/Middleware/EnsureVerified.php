<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->email_verified_at && !$request->user()?->phone_verified_at) {
            return response()->json(['success' => false, 'message' => 'Account not verified.'], 403);
        }
        return $next($request);
    }
}
