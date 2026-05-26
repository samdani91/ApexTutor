<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && !$user->is_active) {
            // Revoke the current token so the client must re-authenticate
            $user->currentAccessToken()->delete();
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support.',
                'suspended' => true,
            ], 403);
        }
        return $next($request);
    }
}
